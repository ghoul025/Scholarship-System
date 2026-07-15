<?php
session_start();
require '../config.php';
require '../includes/batch_helper.php';

// Restrict to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$is_main_admin = false;
$scholars = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT main_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_main_admin = ($user && $user['main_admin'] == 1) && isset($_GET['main_view']);
}

// --- Scholar Filters ---
$search        = $_GET['search'] ?? '';
$course_filter = $_GET['course'] ?? '';
$year_filter   = $_GET['year_level'] ?? '';        // fixed
$type_filter   = $_GET['scholarship_type'] ?? '';  // fixed
$sy_filter     = $_GET['school_year'] ?? '';
$batch_filter  = $_GET['batch'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where  = [];
$params = [];

// Global search (search across multiple scholar fields)
if (!empty($search)) {
    $where[] = "(s.first_name LIKE ? OR s.middle_name LIKE ? OR s.last_name LIKE ? 
                 OR s.course LIKE ? OR s.year_level LIKE ? OR s.scholarship_type LIKE ? OR s.batch LIKE ?)";
    $params = array_merge($params, array_fill(0, 7, "%$search%"));
}

// Specific dropdown filters
if (!empty($course_filter)) {
    $where[] = "s.course = ?";
    $params[] = $course_filter;
}
if (!empty($year_filter)) {
    $where[] = "s.year_level = ?";
    $params[] = $year_filter;
}
if (!empty($type_filter)) {
    $where[] = "s.scholarship_type = ?";
    $params[] = $type_filter;
}
if (!empty($sy_filter)) {
    $where[] = "se.school_year_id = (SELECT id FROM school_years WHERE label = ? LIMIT 1)";
    $params[] = $sy_filter;
}
if (!empty($batch_filter)) {
    $where[] = "s.batch = ?";
    $params[] = $batch_filter;
}
if (!empty($status_filter)) {
    if ($status_filter === 'enrolled') {
        $where[] = "se.school_year_id IS NOT NULL";
    } elseif ($status_filter === 'not_enrolled') {
        $where[] = "se.school_year_id IS NULL";
    }
}

// --- Base Query & Pagination ---
$perPage = 25;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Build base SELECT (no GROUP BY required because LEFT JOIN targets the current SY)
$sqlBase = "FROM scholars s
JOIN users u ON s.user_id = u.id
LEFT JOIN scholar_enrollments se 
       ON se.scholar_id = s.id 
      AND se.school_year_id = (SELECT id FROM school_years WHERE is_current = 1 LIMIT 1)";

// Count total (use same WHERE)
$countSql = "SELECT COUNT(DISTINCT s.id) AS total " . $sqlBase;
if ($where) {
    $countSql .= " WHERE " . implode(" AND ", $where);
}
$countStmt = $conn->prepare($countSql);
$countStmt->execute($params);
$totalRow = $countStmt->fetch(PDO::FETCH_ASSOC);
$totalRows = $totalRow ? (int)$totalRow['total'] : 0;
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Main SELECT with ordering hierarchy: batch (numeric when possible), scholarship_type, course, year_level, fullname
// batch may be stored as text; use REGEXP to only cast numeric batches, non-numeric will sort as NULL (appear last)
$sql = "SELECT s.id,
    s.first_name,
    s.middle_name,
    s.last_name,
    s.course,
    s.year_level,
    s.scholarship_type,
    s.phone AS phone,
    s.sex,
    s.units,
    s.tuition_fee,
    s.batch,
        u.username,
        s.email AS email,
  CASE
    WHEN se.school_year_id IS NOT NULL THEN 'enrolled'
    ELSE 'not_enrolled'
  END AS status,
  se.school_year_id as school_year_id,
  se.enrolled_1st, se.enrolled_2nd
" . $sqlBase;

// Add WHERE clause if filters exist
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Improved ORDER BY with hierarchy
// Note: REGEXP and CAST usage assumes MySQL. Adjust if using different DB.
$sql .= " ORDER BY
    (CASE WHEN s.batch REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN CAST(s.batch AS DECIMAL(10,2)) ELSE NULL END) ASC,
    s.scholarship_type ASC,
    s.course ASC,
    s.year_level ASC,
    s.last_name ASC,
    s.first_name ASC
    LIMIT ? OFFSET ?";

// Execute main query with pagination params
$paramsWithLimit = array_merge($params, [$perPage, $offset]);
$stmt = $conn->prepare($sql);
$stmt->execute($paramsWithLimit);
$scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map enrolled_1st/enrolled_2nd into readable fields used by UI
foreach ($scholars as &$s) {
    $e1 = !empty($s['enrolled_1st']);
    $e2 = !empty($s['enrolled_2nd']);
    if ($e1 && $e2) {
        $s['semester'] = '1st & 2nd';
        $s['enrolled'] = 1;
    } elseif ($e1) {
        $s['semester'] = '1st';
        $s['enrolled'] = 1;
    } elseif ($e2) {
        $s['semester'] = '2nd';
        $s['enrolled'] = 1;
    } else {
        $s['semester'] = '';
        $s['enrolled'] = 0;
    }
}

// Handle scholar registration (unchanged)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_scholar'])) {
    // Modular registration handled in actions/register_scholar.php
    // ...existing code...
}

// Export newly registered scholars to Excel (CSV)
if (isset($_POST['export_accounts']) && !empty($_SESSION['accounts'])) {
    if (!is_dir('../logs')) mkdir('../logs', 0777, true);
    file_put_contents('../logs/actions.log', date('Y-m-d H:i:s') . " - Exported registered scholars\n", FILE_APPEND);
    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="registered_scholars.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Full Name', 'Sex', 'Phone', 'Units', 'Tuition Fee', 'Course', 'Year Level', 'Scholarship Type', 'Password']);
    $sql = "SELECT u.username, s.first_name, s.middle_name, s.last_name, s.phone, s.sex, s.units, s.tuition_fee, s.course, s.year_level, s.scholarship_type, ec.password_plain
            FROM scholars s
            JOIN users u ON s.user_id = u.id
            JOIN exported_credentials ec ON ec.scholar_id = s.id
            ORDER BY s.last_name ASC, s.first_name ASC";
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fullname = trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
        fputcsv($output, [
            $row['username'],
            $fullname,
            $row['sex'],
            $row['phone'],
            $row['units'],
            $row['tuition_fee'],
            $row['course'],
            $row['year_level'],
            $row['scholarship_type'],
            $row['password_plain']
        ]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Scholars</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
      /* small overrides to keep consistent compact table spacing */
      .table-row-hover:hover { background-color: rgba(2,6,23,0.04); }

      /* ensure checkbox column and actions column don't overlap; keep actions outside checkbox area */
      table#scholars-table th:first-child,
      table#scholars-table td:first-child { width: 56px; min-width:56px; max-width:56px; }
      table#scholars-table th:last-child,
      table#scholars-table td:last-child { width: 140px; min-width:140px; max-width:200px; }
      /* ensure action controls are not affected by row clicks */
      .row-action * { pointer-events: auto; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    <?php include __DIR__ . '/../includes/school_years.php'; ?>
   
    <main class="max-w-7xl mx-auto py-5 px-4">
        <?php if ($is_main_admin): ?>
            <div class="bg-blue-100 border-l-4 border-blue-600 text-blue-800 p-3 rounded mb-4 flex items-center gap-3">
                <i class="bi bi-shield-lock text-xl"></i>
                <div>You are viewing as <strong>Main Admin</strong>. This page is <strong>read-only</strong>. No actions are available.</div>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['batch_message'])): ?>
            <div class="mb-4 p-3 rounded bg-green-50 border-l-4 border-green-600 text-green-800"><?= htmlspecialchars($_SESSION['batch_message']); unset($_SESSION['batch_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['batch_error'])): ?>
            <div class="mb-4 p-3 rounded bg-red-50 border-l-4 border-red-600 text-red-800"><?= htmlspecialchars($_SESSION['batch_error']); unset($_SESSION['batch_error']); ?></div>
        <?php endif; ?>

        <?php if (!$is_main_admin): ?>
        <!-- Add Scholar toggle button -->
        <div class="fixed right-5 bottom-14 z-50">
            <button id="toggleAddScholarBtn"
                    class="w-10 h-10 flex items-center justify-center bg-blue-700 hover:bg-blue-800 text-white rounded-full shadow-lg transform transition-transform duration-200 hover:scale-110">
                <i class="bi bi-plus-lg text-xl"></i>
            </button>
        </div>
        <!-- Add Scholar panel (hidden by default) -->
        <div id="addScholarForm" class="hidden fixed right-6 top-20 z-50 w-96 bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-blue-700 to-blue-500 text-white">
                <div class="font-semibold">Add New Scholar</div>
                <button id="closeAddScholar" class="text-white opacity-90 hover:opacity-100">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="p-4 max-h-[70vh] overflow-y-auto">
                <form method="POST" action="actions/register_scholar.php" class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="text" name="phone" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required pattern="[0-9]{11}" maxlength="11" placeholder="e.g. 09XXXXXXXXX">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" name="first_name" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                            <input type="text" name="middle_name" class="mt-1 block w-full border-gray-300 rounded shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sex</label>
                            <select name="sex" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Units</label>
                            <input type="number" name="units" min="1" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tuition Fee</label>
                            <input type="number" name="tuition_fee" min="0" step="0.01" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Course</label>
                            <select name="course" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                                <option value="BSCS">BSCS</option>
                                <option value="BSA">BSA</option>
                                <option value="BSHM">BSHM</option>
                                <option value="BSBA">BSBA</option>
                                <option value="BSTM">BSTM</option>
                                <option value="BEED">BEED</option>
                                <option value="BSED">BSED</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Year Level</label>
                            <select name="year_level" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Scholarship Type</label>
                            <select name="scholarship_type" class="mt-1 block w-full border-gray-300 rounded shadow-sm" required>
                                <option value="TES">TES</option>
                                <option value="TDP">TDP</option>
                                <option value="Others">Listahanan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Batch (optional — number or decimal)</label>
                            <input type="number" name="batch" min="1" step="0.1" class="mt-1 block w-full border-gray-300 rounded shadow-sm" placeholder="e.g. 13 or 13.5">
                        </div>
                    </div>
                    <div>
                        <button type="submit" name="add_scholar" class="w-full bg-blue-700 text-white font-semibold py-2 rounded">Add Scholar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export Batches modal (Tailwind) -->
        <div id="exportBatchesModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white w-96 rounded-lg shadow-lg overflow-hidden">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <div class="font-semibold">Export Batches - Select sheets to include</div>
                    <button data-modal-close="exportBatchesModal" class="text-gray-600 hover:text-gray-900">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="p-4">
                    <form id="export-batches-form" method="POST" action="actions/export_batches.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="text-sm mb-2">Select which batches to export (each will become one sheet):</div>
                        <div class="space-y-1 max-h-52 overflow-auto border rounded p-2">
                            <?php
                            foreach (listBatches($conn) as $b):
                                $formatted_batch = rtrim(rtrim(number_format((float)$b['batch'], 2, '.', ''), '0'), '.');
                                $display = "Batch {$formatted_batch} ({$b['scholarship_type']})";
                                $liquidated = $b['liquidated'] ? 'checked' : '';
                            ?>
                                <div class="flex items-center justify-between gap-2 p-1 rounded hover:bg-gray-50">
                                    <label class="flex items-center gap-2 flex-grow">
                                        <input class="form-checkbox h-4 w-4 text-blue-600" type="checkbox" name="batches[]" value="<?= htmlspecialchars($b['batch'] . '|' . $b['scholarship_type']) ?>">
                                        <span class="text-sm"><?= htmlspecialchars($display) ?></span>
                                    </label>
                                    <label class="flex items-center gap-1 text-sm">
                                        <input type="checkbox" class="liquidation-toggle h-4 w-4 text-green-600" data-batch="<?= htmlspecialchars($b['batch']) ?>" data-type="<?= htmlspecialchars($b['scholarship_type']) ?>" <?= $liquidated ?>>
                                        <span class="text-xs liquidation-label"><?= $b['liquidated'] ? 'Yes' : 'No' ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4 text-right">
                            <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded">Export Selected Batches</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Batch actions toolbar -->
        <?php if (!$is_main_admin): ?>
        <div class="mt-4 mb-3 flex items-center justify-between gap-3">
            <form id="batch-action-form" method="POST" action="actions/batch_actions.php" class="flex items-center gap-2 w-full">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-700">Batch action:</label>
                    <select id="batch-action-select" name="batch_action" class="border-gray-300 rounded px-2 py-1 text-sm">
                        <option value="">-- Select action --</option>
                        <option value="reset">Reset Passwords</option>
                        <option value="delete">Delete Scholars</option>
                        <option value="change_year">Change Year</option>
                        <option value="change_course">Change Course</option>
                        <option value="change_type">Change Scholarship Type</option>
                        <option value="enroll">Enroll (School Year & Semester)</option>
                        <option value="assign_batch">Assign Batch</option>
                    </select>
                </div>

                <!-- contextual controls (hidden by default) -->
                <select id="batch-year-dropdown" name="new_year_level" class="hidden border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">Select year</option>
                    <option>1st Year</option>
                    <option>2nd Year</option>
                    <option>3rd Year</option>
                    <option>4th Year</option>
                </select>

                <select id="batch-course-dropdown" name="new_course" class="hidden border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">Select course</option>
                    <option>BSCS</option><option>BSA</option><option>BSHM</option><option>BSBA</option><option>BSTM</option><option>BEED</option><option>BSED</option>
                </select>

                <select id="batch-type-dropdown" name="new_scholarship_type" class="hidden border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">Select type</option>
                    <option>TES</option><option>TDP</option><option>Listahanan</option>
                </select>

                <select id="batch-sy-dropdown" name="school_year_id" class="hidden border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">Select SY</option>
                    <?php foreach (listSchoolYears() as $sy): ?>
                        <option value="<?= $sy['id'] ?>"><?= htmlspecialchars($sy['label']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="batch-semester-dropdown" name="semester" class="hidden border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">Select sem</option>
                    <option value="1st">1st</option>
                    <option value="2nd">2nd</option>
                </select>

                <input id="batch-name-input" name="new_batch" type="number" min="1" step="0.1" class="hidden border-gray-300 rounded px-2 py-1 text-sm" placeholder="e.g. 13 or 13.5">

                <div class="ml-auto">
                    <button id="batch-action-btn" type="submit" class="bg-blue-700 text-white px-3 py-1 rounded text-sm disabled:opacity-50" disabled>Apply</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        <!-- End batch actions toolbar -->

        <!-- Tailwind Table for Scholars Management -->
        <section class="mt-4 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <i class="bi bi-people-fill text-2xl text-blue-700"></i>
                    <div>

                        <div class="text-lg font-semibold text-gray-900">Scholar Details</div>
                        <div class="text-sm text-gray-500">Manage scholars, batch actions and exports</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div id="selected-count" class="text-sm text-gray-600"></div>
                    <button id="export-batches-btn" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">Export Batches</button>
                    <button id="export-selected-btn" class="bg-emerald-600 text-white px-3 py-1 rounded text-sm disabled:opacity-50" disabled>Export passwords</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="mb-4">
                     <?php include 'includes/scholar_filters.php'; ?>
                    </div>
               
                <table class="min-w-full divide-y divide-gray-200 text-sm" id="scholars-table">
                    <thead class="bg-blue-50 sticky top-0 z-0">
                        <tr>
                            <?php if (!$is_main_admin): ?>
                                <th class="px-3 py-2 text-left w-12">
                                    <input type="checkbox" id="select-all" class="h-4 w-4 text-blue-600">
                                </th>
                            <?php endif; ?>
                            <th class="px-3 py-2 text-left font-medium text-blue-900">Full Name</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Phone</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Sex</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Units</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Tuition Fee</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Course</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Year Level</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Type</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Batch</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Semester</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Status</th>
                            <th class="px-3 py-2 text-center font-medium text-blue-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100" id="scholars-table-body">
                        <?php foreach ($scholars as $scholar): ?>
                            <?php $full = trim($scholar['first_name'] . ' ' . ($scholar['middle_name'] ?: '') . ' ' . $scholar['last_name']); ?>
                            <tr 
                                data-id="<?= $scholar['id'] ?>"
                                data-username="<?= htmlspecialchars($scholar['username']) ?>"
                                data-email="<?= htmlspecialchars($scholar['email'] ?? '') ?>"
                                data-first="<?= htmlspecialchars($scholar['first_name']) ?>"
                                data-middle="<?= htmlspecialchars($scholar['middle_name']) ?>"
                                data-last="<?= htmlspecialchars($scholar['last_name']) ?>"
                                data-name="<?= htmlspecialchars($full) ?>"
                                data-phone="<?= htmlspecialchars($scholar['phone']) ?>"
                                data-sex="<?= htmlspecialchars($scholar['sex']) ?>"
                                data-units="<?= htmlspecialchars($scholar['units']) ?>"
                                data-tuition="<?= htmlspecialchars($scholar['tuition_fee']) ?>"
                                data-course="<?= htmlspecialchars($scholar['course']) ?>"
                                data-year="<?= htmlspecialchars($scholar['year_level']) ?>"
                                data-scholarship-type="<?= htmlspecialchars($scholar['scholarship_type']) ?>"
                                data-schoolyearid="<?= htmlspecialchars($scholar['school_year_id'] ?? '') ?>"
                                data-batch="<?= htmlspecialchars($scholar['batch'] ?? '') ?>"
                                data-semester="<?= htmlspecialchars($scholar['semester'] ?? '') ?>"
                                data-status="<?= htmlspecialchars($scholar['status'] ?? 'not_enrolled') ?>"
                                class="table-row-hover"
                            >
                                <?php if (!$is_main_admin): ?>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <input type="checkbox" name="scholar_ids[]" value="<?= $scholar['id'] ?>" class="row-checkbox h-4 w-4 text-blue-600">
                                    </td>
                                <?php endif; ?>
                                <td class="px-3 py-2 align-middle">
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($full) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($scholar['username']) ?></div>
                                </td>
                                <td class="px-3 py-2 text-center align-middle"><?= htmlspecialchars($scholar['phone']) ?></td>
                                <td class="px-3 py-2 text-center align-middle"><?= htmlspecialchars($scholar['sex']) ?></td>
                                <td class="px-3 py-2 text-center align-middle">
                                    <span class="inline-block bg-blue-100 text-blue-800 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($scholar['units']) ?></span>
                                </td>
                                <td class="px-3 py-2 text-center align-middle">
                                    <span class="inline-block bg-gray-100 text-gray-800 rounded px-2 py-0.5 text-xs">₱<?= number_format($scholar['tuition_fee'],2) ?></span>
                                </td>
                                <td class="px-3 py-2 text-center align-middle">
                                    <span class="inline-block bg-blue-200 text-blue-900 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($scholar['course']) ?></span>
                                </td>
                                <td class="px-3 py-2 text-center align-middle">
                                    <span class="inline-block bg-blue-50 text-blue-900 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($scholar['year_level']) ?></span>
                                </td>
                                <td class="px-3 py-2 text-center align-middle">
                                    <span class="inline-block bg-green-100 text-green-800 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($scholar['scholarship_type']) ?></span>
                                </td>
                                <td class="px-3 py-2 text-center align-middle">
                                    <?php 
                                    $batch_number = !empty($scholar['batch']) ? preg_replace('/[^0-9.]/', '', $scholar['batch']) : '';
                                    if ($batch_number !== '' && is_numeric($batch_number)): ?>
                                        <span class="inline-block bg-yellow-100 text-yellow-800 rounded px-2 py-0.5 text-xs">
                                            <?= htmlspecialchars(rtrim(rtrim(number_format((float)$batch_number, 2, '.', ''), '0'), '.')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-center align-middle"><?= htmlspecialchars($scholar['semester'] ?? '-') ?></td>
                                <td class="px-3 py-2 text-center align-middle">
                                    <?= getScholarStatusLabel($scholar['status'] ?? 'not_enrolled') ?>
                                </td>
                                <td class="px-3 py-2 text-center align-middle row-action">
                                    <?php if (!$is_main_admin): ?>
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" class="action-edit inline-flex items-center justify-center border border-blue-200 text-blue-700 hover:bg-blue-50 rounded px-2 py-1 text-xs" data-id="<?= $scholar['id'] ?>" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <form method="POST" action="actions/scholar_delete.php" onsubmit="return confirm('Delete this scholar? This cannot be undone.');" class="inline-block m-0 p-0">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="scholar_id" value="<?= $scholar['id'] ?>">
                                                <button type="submit" class="inline-flex items-center justify-center border border-red-200 text-red-700 hover:bg-red-50 rounded px-2 py-1 text-xs" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div class="p-4 flex items-center justify-between text-sm text-gray-700">
                <div>
                    <?php
                        $start = $totalRows === 0 ? 0 : ($offset + 1);
                        $end = min($offset + $perPage, $totalRows);
                    ?>
                    Showing <?= $start ?> to <?= $end ?> of <?= $totalRows ?> results
                </div>
                <div class="space-x-2">
                    <?php
                        // build base query with existing GET params except page
                        $qp = $_GET;
                        unset($qp['page']);
                        $baseQuery = http_build_query($qp);
                        $qs = $baseQuery ? $baseQuery . '&' : '';
                        $adjRange = 3; // show +/- pages
                        $startPage = max(1, $page - $adjRange);
                        $endPage = min($totalPages, $page + $adjRange);
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="?<?= $qs ?>page=<?= $page - 1 ?>" class="px-2 py-1 bg-white border rounded hover:bg-gray-50">Prev</a>
                    <?php endif; ?>
                    <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                        <a href="?<?= $qs ?>page=<?= $p ?>" class="px-2 py-1 border rounded <?= $p === $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50' ?>"><?= $p ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= $qs ?>page=<?= $page + 1 ?>" class="px-2 py-1 bg-white border rounded hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <!-- End Tailwind Table -->

        <!-- Edit Scholar Modal (Tailwind) -->
        <div id="editScholarModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/40">
            <div class="bg-white w-full max-w-3xl rounded-lg shadow-lg overflow-hidden">
                <form method="POST" action="actions/edit_scholar.php" id="editScholarForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="scholar_id" id="edit-scholar-id" value="">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <h5 class="text-lg font-semibold">Edit Scholar</h5>
                        <button type="button" data-modal-close="editScholarModal" class="text-gray-600 hover:text-gray-900">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="p-4 max-h-[60vh] overflow-auto grid grid-cols-1 gap-3">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Username</label>
                                <input id="edit-username" name="username" type="text" class="mt-1 block w-full border-gray-300 rounded" required>
                            </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input id="edit-email" name="email" type="email" class="mt-1 block w-full border-gray-300 rounded">
                                </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Name</label>
                                <input id="edit-first_name" name="first_name" type="text" class="mt-1 block w-full border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                                <input id="edit-middle_name" name="middle_name" type="text" class="mt-1 block w-full border-gray-300 rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input id="edit-last_name" name="last_name" type="text" class="mt-1 block w-full border-gray-300 rounded" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input id="edit-phone" name="phone" type="text" class="mt-1 block w-full border-gray-300 rounded" pattern="[0-9]{11}" maxlength="11" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Sex</label>
                                <select id="edit-sex" name="sex" class="mt-1 block w-full border-gray-300 rounded">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Units</label>
                                <input id="edit-units" name="units" type="number" class="mt-1 block w-full border-gray-300 rounded" min="1" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tuition Fee</label>
                                <input id="edit-tuition_fee" name="tuition_fee" type="number" class="mt-1 block w-full border-gray-300 rounded" min="0" step="0.01" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Course</label>
                                <select id="edit-course" name="course" class="mt-1 block w-full border-gray-300 rounded" required>
                                    <option value="BSCS">BSCS</option>
                                    <option value="BSA">BSA</option>
                                    <option value="BSHM">BSHM</option>
                                    <option value="BSBA">BSBA</option>
                                    <option value="BSTM">BSTM</option>
                                    <option value="BEED">BEED</option>
                                    <option value="BSED">BSED</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Year Level</label>
                                <select id="edit-year_level" name="year_level" class="mt-1 block w-full border-gray-300 rounded" required>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>
                            <div class="hidden">
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="edit-status" name="status" class="mt-1 block w-full border-gray-300 rounded">
                                    <option value="not_enrolled">Not Enrolled</option>
                                    <option value="enrolled">Enrolled</option>
                                    <option value="graduated">Graduated</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Scholarship Type</label>
                                <select id="edit-scholarship_type" name="scholarship_type" class="mt-1 block w-full border-gray-300 rounded">
                                    <option value="TES">TES</option>
                                    <option value="TDP">TDP</option>
                                    <option value="Listahanan">Listahanan</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Batch</label>
                                <input id="edit-batch" name="batch" type="number" min="1" step="0.1" class="mt-1 block w-full border-gray-300 rounded shadow-sm" placeholder="e.g. 13 or 13.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">School Year</label>
                                <select id="edit-school_year_id" name="school_year_id" class="mt-1 block w-full border-gray-300 rounded">
                                    <?php foreach (listSchoolYears() as $sy): ?>
                                        <option value="<?= $sy['id'] ?>"><?= htmlspecialchars($sy['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Semester</label>
                                <select id="edit-semester" name="semester" class="mt-1 block w-full border-gray-300 rounded">
                                    <option value="1st">1st</option>
                                    <option value="2nd">2nd</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-4 py-3 border-t">
                        <button type="submit" class="bg-blue-700 text-white px-4 py-1 rounded">Save Changes</button>
                        <button type="button" data-modal-close="editScholarModal" class="bg-gray-200 text-gray-700 px-4 py-1 rounded">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <?php foreach ($scholars as $scholar): ?>
            <div id="statusModal<?= $scholar['id'] ?>" class="hidden fixed inset-0 z-40 items-center justify-center bg-black/40">
                <div class="bg-white rounded shadow p-4 w-80">
                    <form method="POST" action="actions/update_status.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="scholar_id" value="<?= $scholar['id'] ?>">
                        <div class="flex items-center justify-between mb-2">
                            <h5 class="font-semibold">Update Status</h5>
                            <button type="button" data-modal-close="statusModal<?= $scholar['id'] ?>" class="text-gray-600"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div class="mb-2">
                            <select name="status" class="block w-full border-gray-300 rounded mb-2">
                                <option value="enrolled" <?= ($scholar['status'] ?? '') === 'enrolled' ? 'selected' : '' ?>>Enrolled</option>
                                <option value="not_enrolled" <?= ($scholar['status'] ?? '') === 'not_enrolled' ? 'selected' : '' ?>>Not Enrolled</option>
                                <option value="graduated" <?= ($scholar['status'] ?? '') === 'graduated' ? 'selected' : '' ?>>Graduated</option>
                            </select>
                            <label class="block text-xs text-gray-500 mb-1">Enroll for:</label>
                            <?php $current = getCurrentSchoolYear(); ?>
                            <div class="flex gap-2">
                                <select name="school_year_id" class="block w-full border-gray-300 rounded">
                                    <?php foreach (listSchoolYears() as $sy): ?>
                                        <option value="<?= $sy['id'] ?>" <?= ($current && $current['id'] == $sy['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sy['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="semester" class="block w-32 border-gray-300 rounded">
                                    <option value="1st">1st</option>
                                    <option value="2nd">2nd</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="submit" class="bg-blue-700 text-white px-3 py-1 rounded text-sm">Save</button>
                            <button type="button" data-modal-close="statusModal<?= $scholar['id'] ?>" class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Export Preview Modal (Tailwind) -->
        <div id="exportModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/40">
            <div class="bg-white w-11/12 max-w-4xl rounded-lg shadow-lg overflow-hidden">
                <div class="px-4 py-3 border-b flex items-center justify-between">
                    <h5 class="font-semibold">Export Preview</h5>
                    <button data-modal-close="exportModal" class="text-gray-600"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="p-4" id="export-preview-body">
                    <div class="text-center text-gray-500">Select scholars and click Export Selected to preview.</div>
                </div>
                <div class="px-4 py-3 border-t flex items-center justify-end gap-3">
                    <form id="export-selected-form" method="POST" action="actions/export_selected_scholars.php">
                        <input type="hidden" name="scholar_ids" id="export-scholar-ids">
                        <input type="hidden" name="export_selected" value="1">
                        <button type="submit" class="bg-emerald-600 text-white px-4 py-1 rounded">Export to Excel</button>
                    </form>
                    <button data-modal-close="exportModal" class="bg-gray-200 text-gray-700 px-4 py-1 rounded">Close</button>
                </div>
            </div>
        </div>

    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <?php include __DIR__ . '/../includes/titlecase_inputs.php'; ?>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const filterInput = document.getElementById('scholar-filter');
    const clearBtn = document.getElementById('clear-filter');
    const tableBody = document.getElementById('scholars-table-body');
    const selectAll = document.getElementById('select-all');
    const batchForm = document.getElementById('batch-action-form');
    const batchActionSelect = document.getElementById('batch-action-select');
    const yearDropdown = document.getElementById('batch-year-dropdown');
    const courseDropdown = document.getElementById('batch-course-dropdown');
    const typeDropdown = document.getElementById('batch-type-dropdown');
    const batchSy = document.getElementById('batch-sy-dropdown');
    const batchSem = document.getElementById('batch-semester-dropdown');
    const batchNameInput = document.getElementById('batch-name-input');
    const batchBtn = document.getElementById('batch-action-btn');
    const exportBtn = document.getElementById('export-selected-btn');
    const exportPreviewBody = document.getElementById('export-preview-body');
    const exportScholarIds = document.getElementById('export-scholar-ids');
    const selectedCount = document.getElementById('selected-count');
    const toggleAddScholarBtn = document.getElementById('toggleAddScholarBtn');
    const addScholarForm = document.getElementById('addScholarForm');
    const closeAddScholar = document.getElementById('closeAddScholar');
    const editModalEl = document.getElementById('editScholarModal');
    const yearLevelSelect = document.getElementById('edit-year_level');

    function qs(selector) { return document.querySelector(selector); }
    function qsa(selector) { return Array.from(document.querySelectorAll(selector)); }

    // Helper function for right-trim in JavaScript
    function rtrim(str, char) {
        return str.replace(new RegExp(`[${char}]+$`), '');
    }

    // Toggle add scholar panel
    if (toggleAddScholarBtn && addScholarForm) {
        toggleAddScholarBtn.addEventListener('click', () => addScholarForm.classList.toggle('hidden'));
    }
    if (closeAddScholar && addScholarForm) {
        closeAddScholar.addEventListener('click', () => addScholarForm.classList.add('hidden'));
    }

    // Show/hide modal helpers
    function showModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
    function hideModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    // Close modal buttons
    qsa('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => hideModal(btn.dataset.modalClose));
    });

    // Populate Edit Modal
    function populateEditModalFromRow(row) {
        if (!row) return;
        const get = (k) => row.getAttribute('data-' + k) || '';
        const setVal = (id, val) => { const el = document.getElementById(id); if (!el) return; el.value = val; };
        setVal('edit-scholar-id', get('id'));
        setVal('edit-username', get('username'));
    setVal('edit-email', get('email'));
        setVal('edit-first_name', get('first'));
        setVal('edit-middle_name', get('middle'));
        setVal('edit-last_name', get('last'));
        setVal('edit-phone', get('phone'));
        setVal('edit-sex', get('sex'));
        setVal('edit-units', get('units'));
        setVal('edit-tuition_fee', get('tuition'));
        setVal('edit-course', get('course'));
        setVal('edit-year_level', get('year'));
        setVal('edit-scholarship_type', get('scholarship-type'));
        setVal('edit-school_year_id', get('schoolyearid'));
        setVal('edit-semester', get('semester'));
        setVal('edit-status', get('status') || 'not_enrolled');
        
        // Batch extraction
        try {
            const rawBatch = get('batch') || '';
            const cleanedBatch = rawBatch.replace(/[^0-9.]/g, '');
            const batchNum = cleanedBatch ? rtrim(rtrim(Number(cleanedBatch).toFixed(2), '0'), '.') : '';
            setVal('edit-batch', batchNum);
        } catch (err) {
            setVal('edit-batch', '');
        }
    }

    // Edit Scholar buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.action-edit');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();

        const id = btn.getAttribute('data-id');
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;

        populateEditModalFromRow(row);
        showModal('editScholarModal');
    });

    // Filtering
    function getRows() { return tableBody ? Array.from(tableBody.querySelectorAll('tr')) : []; }
    function filterTable() {
        if (!filterInput) return;
        const val = filterInput.value.trim().toLowerCase();
        getRows().forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = val === '' || text.includes(val) ? '' : 'none';
        });
    }
    if (filterInput) {
        filterInput.setAttribute('placeholder', 'Search username, name, course, year, type, status...');
        filterInput.addEventListener('input', filterTable);
    }
    if (clearBtn) {
        clearBtn.addEventListener('click', () => { filterInput.value = ''; filterTable(); });
    }

    // Batch selection utils
    function getCheckboxes() { return Array.from(document.querySelectorAll('.row-checkbox')); }
    function getSelectedIds() { return getCheckboxes().filter(cb => cb.checked).map(cb => cb.value); }
    function updateSelectedUI() {
        const ids = getSelectedIds();
        const count = ids.length;
        if (selectedCount) selectedCount.textContent = count > 0 ? `${count} selected` : '';
        if (exportBtn) exportBtn.disabled = count === 0;
        updateBatchBtn();
    }

    // Select all
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            getCheckboxes().forEach(cb => { cb.checked = selectAll.checked; cb.dispatchEvent(new Event('change')); });
            updateSelectedUI();
        });
    }

    // Row checkbox handling
    document.body.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('row-checkbox')) {
            const tr = e.target.closest('tr');
            if (tr) tr.classList.toggle('bg-blue-50', e.target.checked);
            const all = getCheckboxes();
            if (selectAll) selectAll.checked = all.length > 0 && all.every(x => x.checked);
            updateSelectedUI();
        }
    });

    // Batch form submit validation
    if (batchForm) {
        batchForm.addEventListener('submit', function(e) {
            Array.from(batchForm.querySelectorAll('input[name="scholar_ids[]"]')).forEach(n => n.remove());
            const ids = getSelectedIds();
            ids.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'scholar_ids[]';
                inp.value = id;
                batchForm.appendChild(inp);
            });
            const action = batchActionSelect ? batchActionSelect.value : '';
            if (!action) { alert('Select a batch action.'); e.preventDefault(); return; }
            if (ids.length === 0) { alert('Select at least one scholar.'); e.preventDefault(); return; }
            if (action === 'reset' && !confirm('Reset password for selected scholars to 123456?')) { e.preventDefault(); return; }
            if (action === 'delete' && !confirm('Delete selected scholars? This cannot be undone.')) { e.preventDefault(); return; }
            if (action === 'change_year' && (!yearDropdown || !yearDropdown.value)) { alert('Select a year level.'); e.preventDefault(); return; }
            if (action === 'change_course' && (!courseDropdown || !courseDropdown.value)) { alert('Select a course.'); e.preventDefault(); return; }
            if (action === 'change_type' && (!typeDropdown || !typeDropdown.value)) { alert('Select a scholarship type.'); e.preventDefault(); return; }
            if (action === 'enroll' && (!batchSy || !batchSy.value || !batchSem || !batchSem.value)) { alert('Select both school year and semester.'); e.preventDefault(); return; }
            if (action === 'assign_batch') {
                if (!batchNameInput || !batchNameInput.value.trim()) { 
                    alert('Enter a batch number.'); 
                    e.preventDefault(); 
                    return; 
                }
                if (!/^\d+(\.\d{1,2})?$/.test(batchNameInput.value.trim())) { 
                    alert('Batch must be a number or decimal with up to 2 decimal places (e.g., 13 or 13.5).'); 
                    e.preventDefault(); 
                    return; 
                }
            }
        });
    }

    // Batch action dropdown behaviour
    function hideAllDropdowns() {
        [yearDropdown, courseDropdown, typeDropdown, batchSy, batchSem, batchNameInput].forEach(el => { if (!el) return; el.classList.add('hidden'); el.required = false; });
    }
    if (batchActionSelect) {
        batchActionSelect.addEventListener('change', function() {
            hideAllDropdowns();
            const v = this.value;
            if (v === 'change_year' && yearDropdown) { yearDropdown.classList.remove('hidden'); yearDropdown.required = true; }
            if (v === 'change_course' && courseDropdown) { courseDropdown.classList.remove('hidden'); courseDropdown.required = true; }
            if (v === 'change_type' && typeDropdown) { typeDropdown.classList.remove('hidden'); typeDropdown.required = true; }
            if (v === 'enroll' && batchSy && batchSem) { batchSy.classList.remove('hidden'); batchSy.required = true; batchSem.classList.remove('hidden'); batchSem.required = true; }
            if (v === 'assign_batch' && batchNameInput) { batchNameInput.classList.remove('hidden'); batchNameInput.required = true; }
            updateBatchBtn();
        });
    }

    function updateBatchBtn() {
        if (!batchBtn || !batchActionSelect) return;
        const action = batchActionSelect.value;
        const ids = getSelectedIds();
        let valid = action && ids.length > 0;
        if (action === 'change_year') valid = valid && yearDropdown && yearDropdown.value !== '';
        if (action === 'change_course') valid = valid && courseDropdown && courseDropdown.value !== '';
        if (action === 'change_type') valid = valid && typeDropdown && typeDropdown.value !== '';
        if (action === 'enroll') valid = valid && batchSy && batchSy.value !== '' && batchSem && batchSem.value !== '';
        if (action === 'assign_batch') valid = valid && batchNameInput && batchNameInput.value.trim() && /^\d+(\.\d{1,2})?$/.test(batchNameInput.value.trim());
        batchBtn.disabled = !valid;
    }

    [yearDropdown, courseDropdown, typeDropdown, batchSy, batchSem, batchNameInput].forEach(el => {
        if (el) el.addEventListener('change', updateBatchBtn);
        if (el && el.tagName === 'INPUT') el.addEventListener('input', updateBatchBtn);
    });

    // Liquidation toggle handling
    qsa('.liquidation-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const batch = this.dataset.batch;
            const scholarshipType = this.dataset.type;
            const liquidated = this.checked ? '1' : '0';
            const label = this.nextElementSibling;

            fetch('actions/update_batch_liquidation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `csrf_token=${encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token']) ?>')}&batch=${encodeURIComponent(batch)}&scholarship_type=${encodeURIComponent(scholarshipType)}&liquidated=${liquidated}`
            })
            .then(res => {
                if (!res.ok) throw new Error('Request failed');
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    label.textContent = data.liquidated;
                } else {
                    throw new Error('Invalid response');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Failed to update liquidation status.');
                this.checked = !this.checked; // Revert toggle
                label.textContent = this.checked ? 'Yes' : 'No';
            });
        });
    });

    // Export preview
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const ids = getSelectedIds();
            if (!exportScholarIds) return;
            exportScholarIds.value = ids.join(',');
            if (exportPreviewBody) exportPreviewBody.innerHTML = '<div class="text-center text-gray-500">Loading preview...</div>';
            if (ids.length > 0) {
                fetch('actions/preview_export_scholars.php?preview_export=1&ids=' + ids.join(','))
                    .then(res => res.text())
                    .then(html => { if (exportPreviewBody) exportPreviewBody.innerHTML = html; })
                    .catch(() => { if (exportPreviewBody) exportPreviewBody.innerHTML = '<div class="text-red-600">Preview failed</div>'; });
                showModal('exportModal');
            } else {
                if (exportPreviewBody) exportPreviewBody.innerHTML = '<div class="text-center text-gray-500">Select scholars to preview export.</div>';
                showModal('exportModal');
            }
        });
    }

    // Export batches modal
    const exportBatchesBtn = document.getElementById('export-batches-btn');
    if (exportBatchesBtn) exportBatchesBtn.addEventListener('click', () => showModal('exportBatchesModal'));

    // Export form validation
    const exportForm = document.getElementById('export-batches-form');
    if (exportForm) {
        exportForm.addEventListener('submit', e => {
            const checked = Array.from(exportForm.querySelectorAll('input[type=checkbox][name="batches[]"]')).some(c => c.checked);
            if (!checked) { e.preventDefault(); alert('Select at least one batch to export.'); }
        });
    }

    // Initial UI
    filterTable();
    updateSelectedUI();
    updateBatchBtn();
});
</script>

</body>
</html>
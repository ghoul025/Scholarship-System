<?php
session_start();
require '../config.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); 
    exit;
}

// Flash messages
if (isset($_SESSION['batch_message'])) {
    echo '<div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">'
       . htmlspecialchars($_SESSION['batch_message'])
       . '</div>';
    unset($_SESSION['batch_message']);
}
if (isset($_SESSION['batch_error'])) {
    echo '<div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">'
       . htmlspecialchars($_SESSION['batch_error'])
       . '</div>';
    unset($_SESSION['batch_error']);
}

// Temporary: Button to mail all scholars (visible to admin only)
// (Download ZIP feature moved to main admin dashboard)

// Helper: format batch numbers (remove trailing zeros)
function formatBatch($batch) {
    return is_numeric($batch) ? rtrim(rtrim($batch, '0'), '.') : $batch;
}

// Fetch filter options
try {
    $courses = $conn->query("SELECT DISTINCT course FROM scholars WHERE course IS NOT NULL AND course <> '' ORDER BY course ASC")->fetchAll(PDO::FETCH_COLUMN);
    $years = $conn->query("SELECT DISTINCT year_level FROM scholars WHERE year_level IS NOT NULL AND year_level <> '' ORDER BY year_level ASC")->fetchAll(PDO::FETCH_COLUMN);
    $types = $conn->query("SELECT DISTINCT scholarship_type FROM scholars WHERE scholarship_type IS NOT NULL AND scholarship_type <> '' ORDER BY scholarship_type ASC")->fetchAll(PDO::FETCH_COLUMN);
    $school_years = $conn->query("SELECT id, label FROM school_years ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
    $school_years_map = array_column($school_years, 'label', 'id');
} catch (PDOException $e) {
    error_log("Filter options query failed: " . $e->getMessage());
    $courses = $years = $types = $school_years = $school_years_map = [];
}

// Fetch batches (grouped by batch + scholarship type + school year from latest enrollment)
try {
    $batches = $conn->query("
        SELECT s.batch, s.scholarship_type, se.school_year_id, COUNT(*) as total
        FROM scholars s
        LEFT JOIN scholar_enrollments se 
            ON se.id = (
                SELECT se2.id
                FROM scholar_enrollments se2
                WHERE se2.scholar_id = s.id
                ORDER BY se2.id DESC
                LIMIT 1
            )
        WHERE se.school_year_id IS NOT NULL
        GROUP BY s.batch, s.scholarship_type, se.school_year_id
        ORDER BY se.school_year_id DESC, s.batch DESC, s.scholarship_type ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Batches query failed: " . $e->getMessage());
    $batches = [];
}

// Fetch requirements (tags) - these are what need to be fulfilled
try {
    $required_docs = $conn->query("SELECT DISTINCT document_type FROM requirements WHERE is_required = 1 OR is_required IS NULL")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Requirements query failed: " . $e->getMessage());
    $required_docs = [];
}

$batch_status = [];
foreach ($batches as $batch) {
    $batch_id = $batch['batch'];
    $stype = $batch['scholarship_type'];
    $sy_id = $batch['school_year_id'];

    // Count scholars in this batch + type + school year (latest enrollment)
    try {
        $scholars_in_batch = $conn->prepare("
            SELECT s.id
            FROM scholars s
            LEFT JOIN scholar_enrollments se 
                ON se.id = (
                    SELECT se2.id
                    FROM scholar_enrollments se2
                    WHERE se2.scholar_id = s.id
                    ORDER BY se2.id DESC
                    LIMIT 1
                )
            WHERE s.batch = ? AND s.scholarship_type = ? AND se.school_year_id = ?
        ");
        $scholars_in_batch->execute([$batch_id, $stype, $sy_id]);
        $ids = $scholars_in_batch->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Scholars in batch query failed: " . $e->getMessage());
        $ids = [];
    }

    $complete = 0;
    $incomplete = 0;

    if ($ids) {
        // Fetch approved credentials for scholars in this batch
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("
                SELECT scholar_id, document_type
                FROM documents
                WHERE scholar_id IN ($placeholders)
                AND status = 'Approved'
            ");
            $stmt->execute($ids);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $approved_docs_all = [];
            foreach ($rows as $row) {
                if (!isset($approved_docs_all[$row['scholar_id']])) {
                    $approved_docs_all[$row['scholar_id']] = [];
                }
                $approved_docs_all[$row['scholar_id']][] = $row['document_type'];
            }
        } catch (PDOException $e) {
            error_log("Approved docs query failed: " . $e->getMessage());
            $approved_docs_all = [];
        }

        foreach ($ids as $sid) {
            $approved = $approved_docs_all[$sid] ?? [];
            // Normalize both arrays to lowercase for comparison
            $approved_lc = array_map('mb_strtolower', $approved);
            $required_lc = array_map('mb_strtolower', $required_docs);
            $missing = array_diff($required_lc, $approved_lc);
            if (empty($missing)) {
                $complete++;
            } else {
                $incomplete++;
            }
        }
    }

    $key = "{$batch_id}|{$stype}|{$sy_id}";
    $batch_status[$key] = [
        'total' => $batch['total'],
        'complete' => $complete,
        'incomplete' => $incomplete
    ];
}

// Filter initialization
$search = isset($_GET['search']) ? htmlspecialchars((string)$_GET['search']) : '';
$courses_filter = !empty($_GET['course']) ? (is_array($_GET['course']) ? array_map('htmlspecialchars', array_filter((array)$_GET['course'], 'is_string')) : [htmlspecialchars((string)$_GET['course'])]) : [];
$years_filter = !empty($_GET['year_level']) ? (is_array($_GET['year_level']) ? array_map('htmlspecialchars', array_filter((array)$_GET['year_level'], 'is_string')) : [htmlspecialchars((string)$_GET['year_level'])]) : [];
$types_filter = !empty($_GET['scholarship_type']) ? (is_array($_GET['scholarship_type']) ? array_map('htmlspecialchars', array_filter((array)$_GET['scholarship_type'], 'is_string')) : [htmlspecialchars((string)$_GET['scholarship_type'])]) : [];
$batch_filter = !empty($_GET['batch']) ? (is_array($_GET['batch']) ? array_map('htmlspecialchars', array_filter((array)$_GET['batch'], 'is_string')) : [htmlspecialchars((string)$_GET['batch'])]) : [];
$school_year_filter = !empty($_GET['school_year']) ? (is_array($_GET['school_year']) ? array_map('htmlspecialchars', array_filter((array)$_GET['school_year'], 'is_string')) : [htmlspecialchars((string)$_GET['school_year'])]) : [];
$semester = isset($_GET['semester']) ? htmlspecialchars((string)$_GET['semester']) : 'all';
$enrolled = isset($_GET['enrolled']) ? htmlspecialchars((string)$_GET['enrolled']) : 'all';
$status = isset($_GET['status']) ? htmlspecialchars((string)$_GET['status']) : 'all';

// Build WHERE clause
$where = [];
$params = [];

if ($search) {
    $where[] = "(CONCAT(s.first_name,' ',s.middle_name,' ',s.last_name) LIKE ?)";
    $params[] = "%$search%";
}

$multiFilters = [
    's.course' => $courses_filter,
    's.year_level' => $years_filter,
    's.scholarship_type' => $types_filter,
    's.batch' => $batch_filter,
    'se.school_year_id' => $school_year_filter
];
foreach ($multiFilters as $col => $vals) {
    if ($vals && !in_array('', $vals)) {
        $placeholders = implode(',', array_fill(0, count($vals), '?'));
        $where[] = "$col IN ($placeholders)";
        $params = array_merge($params, $vals);
    }
}

if ($semester !== 'all' && $semester !== '') {
    if ($semester === '1st') {
        $where[] = "se.enrolled_1st = 1";
    } elseif ($semester === '2nd') {
        $where[] = "se.enrolled_2nd = 1";
    }
}

if ($enrolled !== 'all' && $enrolled !== '') {
    if (intval($enrolled) === 1) {
        if ($semester === '1st') $where[] = "se.enrolled_1st = 1";
        elseif ($semester === '2nd') $where[] = "se.enrolled_2nd = 1";
        else $where[] = "(se.enrolled_1st = 1 OR se.enrolled_2nd = 1)";
    } else {
        if ($semester === '1st') $where[] = "(se.enrolled_1st = 0 OR se.enrolled_1st IS NULL)";
        elseif ($semester === '2nd') $where[] = "(se.enrolled_2nd = 0 OR se.enrolled_2nd IS NULL)";
        else $where[] = "((se.enrolled_1st = 0 OR se.enrolled_1st IS NULL) AND (se.enrolled_2nd = 0 OR se.enrolled_2nd IS NULL))";
    }
}

// Build WHERE SQL
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch scholars with latest enrollment
$sql = "
SELECT s.*, se.enrolled_1st, se.enrolled_2nd, se.school_year_id, sy.label AS school_year_label
FROM scholars s
LEFT JOIN scholar_enrollments se 
    ON se.id = (
        SELECT se2.id
        FROM scholar_enrollments se2
        WHERE se2.scholar_id = s.id
        ORDER BY se2.id DESC
        LIMIT 1
    )
LEFT JOIN school_years sy ON se.school_year_id = sy.id
$where_sql
ORDER BY s.year_level, s.course, s.last_name, s.first_name
";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt->execute($params)) {
        error_log("Scholar query failed: " . $sql . " with params: " . implode(', ', $params));
        $scholar_status = [];
    } else {
        $scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Scholar status
        $scholar_status = [];
        try {
            // Fetch all approved credentials for scholars in result set
            $scholar_ids = array_column($scholars, 'id');
            $approved_docs_all = [];
            
            if ($scholar_ids) {
                $placeholders = implode(',', array_fill(0, count($scholar_ids), '?'));
                $stmt = $conn->prepare("
                    SELECT scholar_id, document_type
                    FROM documents
                    WHERE scholar_id IN ($placeholders)
                    AND status = 'Approved'
                ");
                $stmt->execute($scholar_ids);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    if (!isset($approved_docs_all[$row['scholar_id']])) {
                        $approved_docs_all[$row['scholar_id']] = [];
                    }
                    $approved_docs_all[$row['scholar_id']][] = $row['document_type'];
                }
            }
        } catch (PDOException $e) {
            error_log("Approved docs query failed: " . $e->getMessage());
            $approved_docs_all = [];
        }

        foreach ($scholars as $s) {
            $approved = $approved_docs_all[$s['id']] ?? [];
            // Normalize both arrays to lowercase for comparison
            $approved_lc = array_map('mb_strtolower', $approved);
            $required_lc = array_map('mb_strtolower', $required_docs);
            $missing = array_diff($required_lc, $approved_lc);
            // For display, show missing as original case from required_docs
            $missing_display = [];
            foreach ($missing as $miss_lc) {
                foreach ($required_docs as $orig) {
                    if (mb_strtolower($orig) === $miss_lc) {
                        $missing_display[] = $orig;
                        break;
                    }
                }
            }
            $status_value = empty($missing) ? 'Complete' : 'Incomplete';

            if ($status !== 'all' && $status !== $status_value) continue;

            $scholar_status[] = [
                'id' => $s['id'],
                'full_name' => trim("$s[first_name] $s[middle_name] $s[last_name]"),
                'year_level' => $s['year_level'],
                'course' => $s['course'],
                'scholarship_type' => $s['scholarship_type'],
                'batch' => $s['batch'],
                'status' => $status_value,
                'missing_docs' => $missing_display,
                'semester' => (isset($s['enrolled_1st']) && $s['enrolled_1st'] && isset($s['enrolled_2nd']) && $s['enrolled_2nd']) ? '1st & 2nd' : (isset($s['enrolled_1st']) && $s['enrolled_1st'] ? '1st' : (isset($s['enrolled_2nd']) && $s['enrolled_2nd'] ? '2nd' : '')),
                'enrolled' => ((isset($s['enrolled_1st']) && $s['enrolled_1st']) || (isset($s['enrolled_2nd']) && $s['enrolled_2nd'])) ? 1 : 0,
                'school_year' => $s['school_year_label'] ?? 'N/A'
            ];
        }
    }
} catch (PDOException $e) {
    error_log("Scholar query exception: " . $e->getMessage());
    $scholar_status = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Hide elements until Alpine.js initializes */
        [x-cloak] {
            display: none !important;
        }
        /* Creative progress bar styling */
        .progress-bar {
            transition: width 1s ease-in-out, background 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .progress-bar.complete {
            background: linear-gradient(90deg, #22c55e, #10b981);
        }
        .progress-bar.complete.high {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        .progress-bar.incomplete {
            background: linear-gradient(90deg, #9ca3af, #d1d5db);
        }
        /* Pulsing animation for high completion */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(34, 197, 94, 0.5); }
            50% { box-shadow: 0 0 15px rgba(34, 197, 94, 0.8); }
        }
        /* Card hover and focus effects */
        .batch-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            background: linear-gradient(145deg, #ffffff, #f8fafc);
        }
        .batch-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }
        .batch-card:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        /* Icon animation */
        .batch-icon {
            transition: transform 0.3s ease, color 0.3s ease;
        }
        .batch-card:hover .batch-icon {
            transform: scale(1.2) rotate(5deg);
            color: #1e40af;
        }
        /* Badge styling */
        .scholarship-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            background: #e0f2fe;
            color: #1e40af;
        }
        /* Tooltip styling */
        .progress-tooltip {
            position: absolute;
            top: -2rem;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }
        .progress-bar:hover .progress-tooltip {
            opacity: 1;
        }
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .batch-card {
                padding: 1rem;
            }
            .text-lg {
                font-size: 1rem;
            }
            .text-sm {
                font-size: 0.875rem;
            }
            .text-xs {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans">
<?php include __DIR__ . '/includes/navbar.php'; ?>
<div class="container mx-auto px-4 py-8">
    <div>
        <?php // Flash messages are already handled ?>
    </div>
    <!-- System-wide summary cards -->
    <?php
    // Calculate summary metrics
    $total_scholars = count($scholar_status);
    $total_complete = 0;
    $total_incomplete = 0;
    $pending_docs = 0;
    foreach ($scholar_status as $s) {
        if ($s['status'] === 'Complete') {
            $total_complete++;
        } else {
            $total_incomplete++;
            $pending_docs += count($s['missing_docs']);
        }
    }
    $completion_rate = $total_scholars > 0 ? round(($total_complete / $total_scholars) * 100) : 0;

    // Document status analytics (pending/approved/rejected)
    try {
        $doc_status_counts = $conn->query("SELECT status, COUNT(*) as count FROM documents GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        $doc_status_counts = ['Pending'=>0,'Approved'=>0,'Rejected'=>0];
    }
    $pending_files = isset($doc_status_counts['Pending']) ? $doc_status_counts['Pending'] : 0;
    $approved_files = isset($doc_status_counts['Approved']) ? $doc_status_counts['Approved'] : 0;
    $rejected_files = isset($doc_status_counts['Rejected']) ? $doc_status_counts['Rejected'] : 0;

    // Find batches needing attention (incomplete > 25% of batch)
    $attention_batches = [];
    foreach ($batch_status as $key => $stats) {
        $incomplete_percent = $stats['total'] > 0 ? round(($stats['incomplete'] / $stats['total']) * 100) : 0;
        if ($incomplete_percent >= 25 && $stats['incomplete'] > 0) {
            list($batch_id, $stype, $sy_id) = explode('|', $key);
            $attention_batches[] = [
                'batch' => $batch_id,
                'scholarship_type' => $stype,
                'school_year' => $school_years_map[$sy_id] ?? 'Unknown',
                'incomplete' => $stats['incomplete'],
                'total' => $stats['total'],
                'percent' => $incomplete_percent
            ];
        }
    }

    // Find most common missing documents in incomplete scholars
    $missing_doc_count = [];
    foreach ($scholar_status as $s) {
        if ($s['status'] === 'Incomplete') {
            foreach ($s['missing_docs'] as $doc) {
                if (!isset($missing_doc_count[$doc])) $missing_doc_count[$doc] = 0;
                $missing_doc_count[$doc]++;
            }
        }
    }
    arsort($missing_doc_count);
    ?>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 flex items-center min-w-0">
            <div class="text-blue-600 text-2xl mr-3"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="text-base font-bold">Total Scholars</div>
                <div class="text-xl font-extrabold text-gray-900"><?= $total_scholars ?></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 flex items-center min-w-0">
            <div class="text-green-600 text-2xl mr-3"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="text-base font-bold">Completion Rate</div>
                <div class="text-xl font-extrabold text-gray-900"><?= $completion_rate ?>%</div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 flex items-center min-w-0">
            <div class="text-yellow-500 text-2xl mr-3"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="text-base font-bold">Pending Files</div>
                <div class="text-xl font-extrabold text-gray-900"><?= $pending_files ?></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 flex items-center min-w-0">
            <div class="text-green-500 text-2xl mr-3"><i class="bi bi-check2-all"></i></div>
            <div>
                <div class="text-base font-bold">Approved Files</div>
                <div class="text-xl font-extrabold text-gray-900"><?= $approved_files ?></div>
            </div>
        </div>
    </div>
    <!-- Attention Section (Collapsible) -->
    <div class="mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <button type="button" class="w-full text-left flex items-center justify-between font-bold text-red-600 mb-2" onclick="document.getElementById('attention-panel').classList.toggle('hidden')">
                <span><i class="bi bi-exclamation-triangle-fill mr-2"></i> Batches Needing Attention</span>
                <span class="text-xs text-gray-500">(Click to expand/collapse)</span>
            </button>
            <div id="attention-panel" class="hidden">
                <?php if (count($attention_batches) > 0): ?>
                    <ul class="space-y-1">
                        <?php foreach ($attention_batches as $ab): ?>
                            <li class="border-l-4 border-red-500 pl-3 py-1 bg-red-50 rounded text-sm flex flex-wrap items-center">
                                <span class="font-semibold">Batch <?= htmlspecialchars(formatBatch($ab['batch'])) ?></span>
                                <span class="scholarship-badge mx-2"><?= htmlspecialchars($ab['scholarship_type']) ?></span>
                                <span class="text-xs text-gray-500">School Year: <?= htmlspecialchars($ab['school_year']) ?></span>
                                <span class="ml-2 text-red-700 font-bold">Incomplete: <?= $ab['incomplete'] ?>/<?= $ab['total'] ?> (<?= $ab['percent'] ?>%)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-green-600 font-semibold">All batches are in good standing.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Most Common Missing Documents (Collapsible) -->
    <div class="mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <button type="button" class="w-full text-left flex items-center justify-between font-bold text-yellow-600 mb-2" onclick="document.getElementById('missing-docs-panel').classList.toggle('hidden')">
                <span><i class="bi bi-file-earmark-excel-fill mr-2"></i> Most Common Missing Documents</span>
                <span class="text-xs text-gray-500">(Click to expand/collapse)</span>
            </button>
            <div id="missing-docs-panel" class="hidden">
                <?php if (count($missing_doc_count) > 0): ?>
                    <ul class="space-y-1">
                        <?php foreach ($missing_doc_count as $doc => $count): ?>
                            <li class="border-l-4 border-yellow-500 pl-3 py-1 bg-yellow-50 rounded text-sm flex items-center">
                                <span class="font-semibold text-gray-800"><?= htmlspecialchars($doc) ?></span>
                                <span class="ml-2 text-yellow-700 font-bold">Missing: <?= $count ?> scholar<?= $count > 1 ? 's' : '' ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-green-600 font-semibold">No missing documents detected.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/includes/batch_summary.php'; ?>
    <div>
        <?php 
        if (empty($_GET['batch']) && empty($_GET['scholarship_type']) && empty($_GET['school_year'])) {
            ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                <?php foreach ($batches as $batch): 
                    $key = "{$batch['batch']}|{$batch['scholarship_type']}|{$batch['school_year_id']}";
                    $stats = $batch_status[$key] ?? [
                        'total' => $batch['total'],
                        'complete' => 0,
                        'incomplete' => $batch['total']
                    ];
                    $complete_percent = $stats['total'] > 0 ? round(($stats['complete'] / $stats['total']) * 100) : 0;
                    // Dynamic progress bar class
                    $complete_color = $complete_percent >= 75 ? 'complete high' : ($complete_percent >= 50 ? 'complete' : 'complete low');
                    $incomplete_color = 'incomplete';
                    $school_year_label = $school_years_map[$batch['school_year_id']] ?? 'Unknown';
                ?>
                    <a href="?batch=<?= urlencode($batch['batch']) ?>&scholarship_type=<?= urlencode($batch['scholarship_type']) ?>&school_year=<?= urlencode($batch['school_year_id']) ?>"
                       class="batch-card block rounded-xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-200 focus:ring-2 focus:ring-blue-300"
                       tabindex="0">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-700 transition-colors">
                                    Batch <?= formatBatch($batch['batch']) ?>
                                </h3>
                                <span class="scholarship-badge mt-1"><?= htmlspecialchars($batch['scholarship_type']) ?></span>
                                <p class="text-xs text-gray-500 mt-2"><?= htmlspecialchars($school_year_label) ?></p>
                                <p class="text-sm text-gray-600 font-medium mt-1"><?= $stats['total'] ?> scholars</p>
                            </div>
                            <div class="text-blue-600 text-2xl batch-icon">
                                <i class="bi bi-folder-fill"></i>
                            </div>
                        </div>
                        <!-- Progress Bars -->
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-xs font-medium text-gray-700 mb-1">
                                    <span>Complete</span>
                                    <span><?= $stats['complete'] ?> / <?= $stats['total'] ?></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 relative">
                                    <div class="progress-bar <?= $complete_color ?> h-3 rounded-full" 
                                         style="width: <?= $complete_percent ?>%">
                                        <span class="progress-tooltip"><?= $complete_percent ?>% Complete</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-medium text-gray-700 mb-1">
                                    <span>Incomplete</span>
                                    <span><?= $stats['incomplete'] ?> / <?= $stats['total'] ?></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 relative">
                                    <div class="progress-bar <?= $incomplete_color ?> h-3 rounded-full" 
                                         style="width: <?= 100 - $complete_percent ?>%">
                                        <span class="progress-tooltip"><?= 100 - $complete_percent ?>% Incomplete</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 text-right">
                            <span class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">View Details →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php
        } else {
            include __DIR__ . '/includes/scholar_table.php'; 
        }
        ?>
    </div>
</div>
<script src="../js/dashboard.js"></script>
<script>
    // Animate progress bars on page load
    document.addEventListener('DOMContentLoaded', () => {
        const bars = document.querySelectorAll('.progress-bar');
        bars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 200); // Increased delay for dramatic effect
        });
    });
</script>
</body>
</html>
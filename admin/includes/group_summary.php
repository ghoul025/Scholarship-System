<?php
// Ensure $conn is available
if (!isset($conn)) {
    die("Database connection not available.");
}

// Get filters, handling both array and string inputs
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

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch school year labels for display
try {
    $sy_stmt = $conn->prepare("SELECT id, label FROM school_years ORDER BY start_date DESC");
    $sy_stmt->execute();
    $school_years = array_column($sy_stmt->fetchAll(PDO::FETCH_ASSOC), 'label', 'id');
} catch (PDOException $e) {
    error_log("School years query failed: " . $e->getMessage());
    $school_years = [];
}

// Prepare grouped data dynamically
try {
    $groups_stmt = $conn->prepare("
        SELECT s.year_level, s.course, s.scholarship_type, se.school_year_id, COUNT(DISTINCT s.id) AS total
        FROM scholars s
        LEFT JOIN scholar_enrollments se 
            ON se.id = (
                SELECT se2.id
                FROM scholar_enrollments se2
                WHERE se2.scholar_id = s.id
                ORDER BY se2.id DESC
                LIMIT 1
            )
        $where_sql
        GROUP BY s.year_level, s.course, s.scholarship_type, se.school_year_id
        ORDER BY se.school_year_id DESC, s.year_level ASC, s.course ASC, s.scholarship_type ASC
    ");
    if (!$groups_stmt->execute($params)) {
        error_log("Group query failed: " . $groups_stmt->queryString . " with params: " . implode(', ', $params));
        $groups = [];
    } else {
        $groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Group query exception: " . $e->getMessage());
    $groups = [];
}

// Calculate total number of scholars
$total_scholars = array_sum(array_column($groups, 'total'));

// Get current school year and batch/scholarship type for display
$current_school_year = !empty($school_year_filter) ? ($school_years[$school_year_filter[0]] ?? 'Unknown School Year') : 'No School Year Selected';
$batch_display = !empty($batch_filter) ? $batch_filter[0] : 'N/A';
$stype_display = !empty($types_filter) ? $types_filter[0] : 'N/A';
?>

<!-- Floating Toggle & Panel -->
<div x-data="{ open: false }" class="fixed bottom-4 right-4 z-50">
  <!-- Floating Toggle Button -->
  <button 
    @click="open = !open"
    class="bg-blue-600 text-white shadow-md rounded-full w-12 h-12 flex items-center justify-center hover:bg-blue-700 transition"
    title="Toggle Scholarship Group Summary">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>

  <!-- Floating Collapsible Panel -->
  <div 
    x-show="open" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="absolute bottom-16 right-0 w-80 bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200"
    style="display: none;" x-cloak
  >
    <!-- Header -->
    <div class="flex items-center justify-between bg-blue-600 text-white px-3 py-2">
      <div class="flex-1 min-w-0">
        <h6 class="font-semibold text-sm flex items-center space-x-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <span>Batch <?= htmlspecialchars($batch_display) ?> (<?= htmlspecialchars($stype_display) ?>)</span>
        </h6>
        <p class="text-xs opacity-90 mt-0.5 truncate" title="School Year: <?= htmlspecialchars($current_school_year) ?>">
          📚 <?= htmlspecialchars($current_school_year) ?>
        </p>
      </div>
      <button @click="open = false" class="text-white hover:text-gray-200 p-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Body -->
    <div class="max-h-72 overflow-y-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-100 sticky top-0 text-gray-700 text-xs">
          <tr>
            <th class="px-2 py-1 text-left w-1/5">Year</th>
            <th class="px-1 py-1 text-left w-1/3">Course</th>
            <th class="px-1 py-1 text-left w-1/3">Type</th>
            <th class="px-2 py-1 text-left w-1/3">School Year</th>
            <th class="px-2 py-1 text-right w-1/6">Count</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($groups)): ?>
            <tr>
              <td colspan="5" class="text-center text-gray-400 py-2 text-xs">No groups found</td>
            </tr>
          <?php else: ?>
            <?php foreach ($groups as $g): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="px-2 py-1 font-semibold text-gray-700 truncate" title="<?= htmlspecialchars($g['year_level']) ?>">
                  <?= htmlspecialchars($g['year_level']) ?>
                </td>
                <td class="px-1 py-1 truncate text-gray-700" title="<?= htmlspecialchars($g['course']) ?>">
                  <?= htmlspecialchars($g['course']) ?>
                </td>
                <td class="px-1 py-1 truncate text-gray-500" title="<?= htmlspecialchars($g['scholarship_type']) ?>">
                  <?= htmlspecialchars($g['scholarship_type']) ?>
                </td>
                <td class="px-1 py-1 truncate text-gray-500" title="<?= htmlspecialchars($school_years[$g['school_year_id']] ?? 'N/A') ?>">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800 font-medium">
                    <?= htmlspecialchars($school_years[$g['school_year_id']] ?? 'N/A') ?>
                  </span>
                </td>
                <td class="px-2 py-1 text-right">
                  <span class="bg-blue-100 text-blue-600 text-xs font-medium px-2 py-0.5 rounded-full">
                    <?= $g['total'] ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div class="bg-gray-50 text-center py-2 border-t text-xs text-gray-500">
      Total Groups: <?= count($groups) ?> | Total Scholars: <?= $total_scholars ?>
    </div>
  </div>
</div>
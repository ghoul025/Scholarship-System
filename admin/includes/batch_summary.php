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
    class="bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/25 rounded-full w-12 h-12 flex items-center justify-center transition-all duration-200 transform hover:scale-105 active:scale-95"
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
    class="absolute bottom-16 right-0 w-96 bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-200 ring-1 ring-black/5"
  >
    <!-- Header -->
    <div class="flex items-start justify-between bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3 relative overflow-hidden">
      <div class="flex-1 min-w-0">
        <h6 class="font-bold text-sm tracking-tight flex items-center space-x-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <span>Batch <?= htmlspecialchars(formatBatch($batch_display)) ?> (<?= htmlspecialchars($stype_display) ?>)</span>
        </h6>
        <p class="text-xs opacity-90 mt-0.5 truncate" title="School Year: <?= htmlspecialchars($current_school_year) ?>">
          📚 School Year: <?= htmlspecialchars($current_school_year) ?>
        </p>
      </div>
      <button @click="open = false" class="ml-2 text-white hover:text-gray-200 p-1 rounded-full hover:bg-white/10 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
      <!-- Decorative element -->
      <div class="absolute top-0 right-0 w-20 h-20 bg-white/5 rounded-full -mr-10 -mt-10"></div>
    </div>

    <!-- Body -->
    <div class="max-h-80 overflow-y-auto">
      <table class="w-full text-sm">
        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-xs text-gray-700 uppercase tracking-wider">Year Level</th>
            <th class="px-4 py-3 text-left font-semibold text-xs text-gray-700 uppercase tracking-wider">Course</th>
            <th class="px-4 py-3 text-left font-semibold text-xs text-gray-700 uppercase tracking-wider">Type</th>
            <th class="px-4 py-3 text-left font-semibold text-xs text-gray-700 uppercase tracking-wider">School Year</th>
            <th class="px-4 py-3 text-right font-semibold text-xs text-gray-700 uppercase tracking-wider">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <?php if (!empty($groups)): ?>
            <?php $row_index = 0; ?>
            <?php foreach ($groups as $group): ?>
              <?php $row_index++; ?>
              <tr class="hover:bg-blue-50/50 transition-colors duration-150 <?= ($row_index % 2 === 0 ? 'bg-gray-50/50' : '') ?>">
                <td class="px-4 py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($group['year_level']) ?></td>
                <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($group['course']) ?></td>
                <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($group['scholarship_type']) ?></td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800 font-medium">
                    <?= htmlspecialchars($school_years[$group['school_year_id']] ?? 'N/A') ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                  <span class="inline-flex items-center justify-end">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <?= $group['total'] ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <p class="mt-2">No groups found</p>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php if (!empty($groups)): ?>
        <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-gray-200">
          <div class="flex items-center justify-between text-sm font-semibold text-gray-900">
            <span>Total Scholars:</span>
            <span class="text-blue-600 flex items-center space-x-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
              </svg>
              <?= $total_scholars ?>
            </span>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
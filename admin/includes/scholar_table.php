<?php
// Ensure dependencies are loaded
if (!isset($conn)) {
    die("Database connection not available.");
}
if (!function_exists('formatBatch')) {
    function formatBatch($batch) {
        return is_numeric($batch) ? rtrim(rtrim($batch, '0'), '.') : $batch;
    }
}

// Initialize filter variables
$search = isset($_GET['search']) ? htmlspecialchars((string)$_GET['search']) : '';
$semester = isset($_GET['semester']) ? htmlspecialchars((string)$_GET['semester']) : 'all';
$enrolled = isset($_GET['enrolled']) ? htmlspecialchars((string)$_GET['enrolled']) : 'all';
$status = isset($_GET['status']) ? htmlspecialchars((string)$_GET['status']) : 'all';
$batch_filter = !empty($_GET['batch']) ? (is_array($_GET['batch']) ? array_map('htmlspecialchars', array_filter((array)$_GET['batch'], 'is_string')) : [htmlspecialchars((string)$_GET['batch'])]) : [];
$stype_filter = !empty($_GET['scholarship_type']) ? (is_array($_GET['scholarship_type']) ? array_map('htmlspecialchars', array_filter((array)$_GET['scholarship_type'], 'is_string')) : [htmlspecialchars((string)$_GET['scholarship_type'])]) : [];
$courses_filter = !empty($_GET['course']) ? (is_array($_GET['course']) ? array_map('htmlspecialchars', array_filter((array)$_GET['course'], 'is_string')) : [htmlspecialchars((string)$_GET['course'])]) : [];
$years_filter = !empty($_GET['year_level']) ? (is_array($_GET['year_level']) ? array_map('htmlspecialchars', array_filter((array)$_GET['year_level'], 'is_string')) : [htmlspecialchars((string)$_GET['year_level'])]) : [];
$school_year_filter = !empty($_GET['school_year']) ? (is_array($_GET['school_year']) ? array_map('htmlspecialchars', array_filter((array)$_GET['school_year'], 'is_string')) : [htmlspecialchars((string)$_GET['school_year'])]) : [];

$has_filters = (bool) ($search || $courses_filter || $years_filter || $semester !== 'all' || $enrolled !== 'all' || $status !== 'all');

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 25;
$total_scholars = isset($scholar_status) ? count($scholar_status) : 0;
$total_pages = ceil($total_scholars / $per_page);
$offset = ($page - 1) * $per_page;
$sliced_scholars = isset($scholar_status) ? array_slice($scholar_status, $offset, $per_page) : [];

// Fetch filter options dynamically if not provided
if (!isset($courses)) {
    try {
        $stmt = $conn->prepare("SELECT DISTINCT course FROM scholars WHERE course IS NOT NULL AND course <> '' ORDER BY course ASC");
        $stmt->execute();
        $courses = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'course');
    } catch (PDOException $e) {
        error_log("Courses query failed: " . $e->getMessage());
        $courses = [];
    }
}
if (!isset($years)) {
    try {
        $stmt = $conn->prepare("SELECT DISTINCT year_level FROM scholars WHERE year_level IS NOT NULL AND year_level <> '' ORDER BY year_level ASC");
        $stmt->execute();
        $years = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'year_level');
    } catch (PDOException $e) {
        error_log("Years query failed: " . $e->getMessage());
        $years = [];
    }
}

// Take the first value for display purposes
$batch_display = !empty($batch_filter) ? $batch_filter[0] : '';
$stype_display = !empty($stype_filter) ? $stype_filter[0] : '';

// Build base URL for pagination and clears, preserving school_year
$base_url = 'dashboard.php?batch=' . urlencode(implode(',', $batch_filter)) . 
            '&scholarship_type=' . urlencode(implode(',', $stype_filter)) . 
            (!empty($school_year_filter) ? '&school_year=' . urlencode(implode(',', $school_year_filter)) : '');

// Debug: Ensure scholar_status is set
if (!isset($scholar_status)) {
    $scholar_status = [];
}
?>

<style>
    .filter-container select {
        height: 32px;
        font-size: 0.75rem;
        line-height: 1rem;
    }
    .filter-container .search-input {
        height: 32px;
        font-size: 0.75rem;
    }
    .filter-container .toggle-button {
        transition: transform 0.2s ease-in-out;
    }
    .filter-container .toggle-button.active {
        transform: rotate(180deg);
    }
    .filter-container .advanced-filters {
        transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
        max-height: 0;
        opacity: 0;
        overflow: hidden;
    }
    .filter-container .advanced-filters.active {
        max-height: 500px;
        opacity: 1;
    }
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .pagination a, .pagination span {
        padding: 0.25rem 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        text-decoration: none;
        color: #374151;
        font-size: 0.75rem;
    }
    .pagination a:hover {
        background-color: #f3f4f6;
    }
    .pagination .current {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    .pagination .disabled {
        color: #9ca3af;
        cursor: not-allowed;
    }
</style>

<?php if (!empty($batch_display) && !empty($stype_display)): ?>
<div class="mt-3 max-w-7xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm">
    <!-- Header with Back Button -->
    <div class="flex justify-between items-center bg-blue-600 text-white px-2 py-1.5 rounded-t-xl">
        <span class="flex items-center space-x-1.5 font-medium text-sm">
            <i class="bi bi-people-fill"></i>
            <span>
                Batch <?= htmlspecialchars(formatBatch($batch_display)) ?> (<?= htmlspecialchars($stype_display) ?>) - Scholars
            </span>
        </span>
        <a href="dashboard.php" 
           class="inline-flex items-center bg-white text-blue-600 px-2 py-1 rounded-md font-medium text-xs hover:bg-gray-100 transition">
            <i class="bi bi-arrow-left mr-1"></i> Back
        </a>
    </div>

    <!-- Advanced Filters -->
    <form method="GET" action="dashboard.php" id="scholar-filters-form" class="bg-white filter-container">
        <input type="hidden" name="batch" value="<?= htmlspecialchars(implode(',', $batch_filter)) ?>">
        <input type="hidden" name="scholarship_type" value="<?= htmlspecialchars(implode(',', $stype_filter)) ?>">
        <input type="hidden" name="school_year" value="<?= htmlspecialchars(implode(',', $school_year_filter)) ?>">
        <div class="flex items-center justify-between px-2 py-1.5">
            <div class="flex-1 flex items-center gap-1.5">
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search by name..."
                    class="flex-1 search-input rounded-full border-gray-300 shadow-sm px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                >
                <button type="submit"
                    class="bg-blue-600 text-white px-2 py-1 rounded-full text-xs shadow hover:bg-blue-700 transition">
                    Search
                </button>
                <?php if ($has_filters): ?>
                    <a href="<?= htmlspecialchars($base_url) ?>" 
                       class="px-2 py-1 text-xs text-gray-600 hover:text-gray-900">Reset</a>
                <?php endif; ?>
            </div>
            <button type="button" id="toggle-advanced-filters" class="ml-1.5 text-gray-500 hover:text-gray-700 text-xs toggle-button">
                Filters <span class="inline-block">&#9662;</span>
            </button>
        </div>

        <div id="advanced-filters" class="advanced-filters border-t border-gray-100 px-2 py-1.5 <?= $has_filters ? 'active' : '' ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-1.5">
                <!-- Course -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Course</label>
                    <select name="course" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= in_array($c, $courses_filter) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Year Level -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Year Level</label>
                    <select name="year_level" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Year Levels</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= htmlspecialchars($y) ?>" <?= in_array($y, $years_filter) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($y) ?><?= is_numeric($y) ? getOrdinal($y) . ' Year' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Semester -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Semester</label>
                    <select name="semester" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" <?= $semester === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="1st" <?= $semester === '1st' ? 'selected' : '' ?>>1st</option>
                        <option value="2nd" <?= $semester === '2nd' ? 'selected' : '' ?>>2nd</option>
                    </select>
                </div>

                <!-- Enrolled -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Enrolled</label>
                    <select name="enrolled" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" <?= $enrolled === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="1" <?= $enrolled === '1' ? 'selected' : '' ?>>Enrolled</option>
                        <option value="0" <?= $enrolled === '0' ? 'selected' : '' ?>>Not Enrolled</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="Complete" <?= $status === 'Complete' ? 'selected' : '' ?>>Complete</option>
                        <option value="Incomplete" <?= $status === 'Incomplete' ? 'selected' : '' ?>>Incomplete</option>
                    </select>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1.5 mt-2">
                <button type="submit" class="bg-blue-600 text-white px-2 py-1 rounded-lg text-xs shadow hover:bg-blue-700 transition">
                    Apply Filters
                </button>
                <a href="<?= htmlspecialchars($base_url) ?>" 
                   class="px-2 py-1 text-xs rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
                    Clear Filters
                </a>
            </div>
        </div>
    </form>

    <!-- Table Container -->
    <div class="overflow-x-auto p-2">
        <table class="min-w-full text-xs text-left divide-y divide-gray-200">
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">Name</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">Year</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">Course</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">Type</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">Status</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">School Year</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">Semester</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">Enrolled</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase">Missing Docs</th>
                    <th class="px-2 py-1.5 font-semibold text-gray-700 uppercase text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (!empty($sliced_scholars)): ?>
                    <?php foreach ($sliced_scholars as $i => $s): 
                        $missing_docs = $s['missing_docs'] ?? [];
                        $has_missing = !empty($missing_docs);
                        $status_html = $has_missing 
                            ? '<span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-1.5 py-0.5 rounded-full">Incomplete</span>'
                            : '<span class="bg-green-100 text-green-800 text-xs font-semibold px-1.5 py-0.5 rounded-full">Complete</span>';
                        $enrolled_html = $s['enrolled'] 
                            ? '<span class="bg-green-100 text-green-800 text-xs font-semibold px-1.5 py-0.5 rounded-full">Yes</span>'
                            : '<span class="bg-red-100 text-red-800 text-xs font-semibold px-1.5 py-0.5 rounded-full">No</span>';
                    ?>
                        <tr class="<?= ($offset + $i) % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?> hover:bg-blue-50 transition-colors">
                            <td class="px-2 py-1.5 font-medium"><?= htmlspecialchars($s['full_name']) ?></td>
                            <td class="px-2 py-1.5"><?= htmlspecialchars($s['year_level']) ?></td>
                            <td class="px-2 py-1.5"><?= htmlspecialchars($s['course']) ?></td>
                            <td class="px-2 py-1.5"><?= htmlspecialchars($s['scholarship_type']) ?></td>
                            <td class="px-2 py-1.5"><?= $status_html ?></td>
                            <td class="px-2 py-1.5"><?= htmlspecialchars($s['school_year'] ?? 'N/A') ?></td>
                            <td class="px-2 py-1.5"><?= htmlspecialchars($s['semester'] ?? 'N/A') ?></td>
                            <td class="px-2 py-1.5"><?= $enrolled_html ?></td>
                            <td class="px-2 py-1.5">
                                <?php if ($has_missing): ?>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($missing_docs as $doc_tag): ?>
                                            <span class="bg-yellow-300 text-yellow-900 text-xs font-medium px-1.5 py-0.5 rounded-full" 
                                                  title="<?= htmlspecialchars($doc_tag) ?>">
                                                <?= htmlspecialchars($doc_tag) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">Complete</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 py-1.5 text-center">
                                <a href="scholar_documents.php?scholar_id=<?= htmlspecialchars($s['id'] ?? '') ?>"
                                   class="inline-flex items-center px-2 py-1 text-xs bg-blue-500 text-white rounded-md hover:bg-blue-600 transition"
                                   title="View Documents">
                                    <i class="bi bi-folder2-open text-sm"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-gray-500 text-xs">No scholars found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <a href="<?= htmlspecialchars($base_url . '&page=' . max(1, $page - 1)) ?>" 
                   class="<?= $page <= 1 ? 'disabled' : '' ?>">Previous</a>
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                if ($start_page > 1): ?>
                    <a href="<?= htmlspecialchars($base_url . '&page=1') ?>">1</a>
                    <?php if ($start_page > 2): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="<?= htmlspecialchars($base_url . '&page=' . $i) ?>" 
                       class="<?= $i === $page ? 'current' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span>...</span>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($base_url . '&page=' . $total_pages) ?>"><?= $total_pages ?></a>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($base_url . '&page=' . min($total_pages, $page + 1)) ?>" 
                   class="<?= $page >= $total_pages ? 'disabled' : '' ?>">Next</a>
                <span>Page <?= $page ?> of <?= $total_pages ?> (<?= $total_scholars ?> total)</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById("toggle-advanced-filters").addEventListener("click", function() {
    const advancedFilters = document.getElementById("advanced-filters");
    const isActive = !advancedFilters.classList.contains("active");
    advancedFilters.classList.toggle("active", isActive);
    this.classList.toggle("active", isActive);
});
</script>

<?php else: ?>
    <div class="mt-3 max-w-7xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm p-2 text-center text-gray-500 text-xs">
        Please select a valid batch and scholarship type.
    </div>
<?php endif; ?>

<?php
function getOrdinal($num) {
    $num = (int)$num;
    if ($num % 100 >= 11 && $num % 100 <= 13) return "th";
    switch ($num % 10) {
        case 1: return "st";
        case 2: return "nd";
        case 3: return "rd";
        default: return "th";
    }
}
?>
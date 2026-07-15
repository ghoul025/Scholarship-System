<?php
// includes/scholar_filters.php
// Single form filter UI compatible with filters.php logic in manage_scholars.php
// Assumes $conn (PDO) is already available from manage_scholars.php

$search        = $_GET['search'] ?? '';
$course_filter = $_GET['course'] ?? '';
$year_filter   = $_GET['year_level'] ?? '';
$type_filter   = $_GET['scholarship_type'] ?? '';
$batch_filter  = $_GET['batch'] ?? '';
$status_filter = $_GET['status'] ?? '';

$has_filters = (bool) ($search || $course_filter || $year_filter || $type_filter || $batch_filter || $status_filter);

// --- Fetch distinct values using PDO ---
function getDistinctOptions($conn, $column) {
    $options = [];
    $stmt = $conn->prepare("SELECT DISTINCT $column FROM scholars WHERE $column IS NOT NULL AND $column <> '' ORDER BY $column ASC");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $options[] = $row[$column];
    }
    return $options;
}

$courses = getDistinctOptions($conn, "course");
$years = getDistinctOptions($conn, "year_level");
$types = getDistinctOptions($conn, "scholarship_type");
$batches = getDistinctOptions($conn, "batch");
$statuses = getDistinctOptions($conn, "status");
?>

<form method="GET" action="manage_scholars.php" id="scholar-filters-form" class="bg-white border border-gray-200 rounded-xl shadow-sm mb-4">
    <div class="flex items-center justify-between px-4 py-3">
        <div class="flex-1 flex items-center gap-2">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search scholars..."
                class="flex-1 rounded-full border-gray-300 shadow-sm text-sm px-4 py-2 focus:ring-blue-500 focus:border-blue-500"
            >
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm shadow hover:bg-blue-700 transition">
                Search
            </button>

            <?php if ($has_filters): ?>
                <a href="manage_scholars.php" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
            <?php endif; ?>
        </div>

        <button type="button" id="toggle-advanced-filters" class="ml-2 text-gray-500 hover:text-gray-700 text-sm">
            Advanced ▾
        </button>
    </div>

    <div id="advanced-filters" class="<?= $has_filters ? '' : 'hidden' ?> border-t border-gray-100 px-4 py-3">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">

            <!-- Course -->
            <select name="course" class="rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Courses</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course) ?>" <?= $course_filter == $course ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Year Level -->
            <select name="year_level" class="rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Year Levels</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?= htmlspecialchars($year) ?>" <?= $year_filter == $year ? 'selected' : '' ?>>
                        <?= htmlspecialchars($year) ?><?= is_numeric($year) ? getOrdinal($year) . " Year" : "" ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Scholarship Type -->
            <select name="scholarship_type" class="rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Scholarship Types</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>" <?= $type_filter == $type ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Batch -->
            <select name="batch" class="rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Batches</option>
                <?php foreach ($batches as $batch): ?>
                
         <?php 
        if (is_numeric($batch)) {
          // remove trailing zeros and decimal point if not needed
         $batch_display = rtrim(rtrim($batch, '0'), '.');
            } else {
        $batch_display = $batch;
        }
    ?>
<option value="<?= htmlspecialchars($batch) ?>" <?= $batch_filter == $batch ? 'selected' : '' ?>>
    Batch <?= htmlspecialchars($batch_display) ?>
</option>



                <?php endforeach; ?>
            </select>

            <!-- Status -->
            <select name="status" class="rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Status</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= $status_filter == $status ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace("_"," ",$status)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

        </div>

        <!-- Actions -->
        <div class="col-span-1 md:col-span-6 flex items-center gap-2 mt-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm shadow hover:bg-blue-700 transition">
                Apply Filters
            </button>

            <a href="manage_scholars.php" class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100">
                Clear Filters
            </a>
        </div>
    </div>
</form>

<script>
document.getElementById("toggle-advanced-filters").addEventListener("click", () => {
    document.getElementById("advanced-filters").classList.toggle("hidden");
});
</script>

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

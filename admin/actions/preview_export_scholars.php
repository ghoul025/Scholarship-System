<?php
session_start();
require '../../config.php';

// Restrict to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="bg-red-50 border-l-4 border-red-600 text-red-800 p-3 rounded mb-4 flex items-center gap-3">
            <i class="bi bi-exclamation-circle text-xl"></i>
            <div>Unauthorized access. Please log in as an admin.</div>
          </div>';
    exit;
}

// Validate and sanitize input IDs
$ids = [];
if (isset($_GET['ids'])) {
    $ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));
}

if (empty($ids)) {
    echo '<div class="bg-yellow-50 border-l-4 border-yellow-600 text-yellow-800 p-3 rounded mb-4 flex items-center gap-3">
            <i class="bi bi-exclamation-triangle text-xl"></i>
            <div>No scholars selected for preview. Please select at least one scholar.</div>
          </div>';
    exit;
}

// Prepare and execute query
$in = implode(',', array_fill(0, count($ids), '?'));
$sql = "SELECT u.username, s.first_name, s.middle_name, s.last_name, s.phone, s.sex, 
               s.course, s.year_level, s.scholarship_type, ec.password_plain
        FROM scholars s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN exported_credentials ec ON ec.scholar_id = s.id
        WHERE s.id IN ($in)
        ORDER BY s.last_name ASC, s.first_name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute($ids);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo '<div class="bg-blue-50 border-l-4 border-blue-600 text-blue-800 p-3 rounded mb-4 flex items-center gap-3">
            <i class="bi bi-info-circle text-xl"></i>
            <div>No data found for the selected scholars.</div>
          </div>';
    exit;
}

// Check if all rows have null password_plain
$allNull = true;
foreach ($rows as $row) {
    if (!empty($row['password_plain'])) {
        $allNull = false;
        break;
    }
}
if ($allNull) {
    echo '<div class="bg-yellow-50 border-l-4 border-yellow-600 text-yellow-800 p-3 rounded mb-4 flex items-center gap-3">
            <i class="bi bi-exclamation-triangle text-xl"></i>
            <div>No credentials found for the selected scholars. They may not have been registered or exported yet.</div>
          </div>';
    exit;
}
?>

<div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
    <div class="p-4 border-b flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <i class="bi bi-file-earmark-spreadsheet text-2xl text-blue-700"></i>
            <div>
                <div class="text-lg font-semibold text-gray-900">Export Preview</div>
                <div class="text-sm text-gray-500">Preview of selected scholars for password distribution</div>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-[70vh]" role="region" aria-labelledby="table-caption">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <caption id="table-caption" class="sr-only">Preview of selected scholars for password distribution</caption>
            <thead class="bg-blue-50 sticky top-0 z-10">
                <tr>
                    <?php
                    $headers = ['Username', 'Full Name', 'Phone', 'Sex', 'Course', 'Year Level', 'Scholarship Type', 'Password'];
                    foreach ($headers as $header) {
                        echo '<th class="px-3 py-2 text-center font-medium text-blue-900">' . htmlspecialchars($header) . '</th>';
                    }
                    ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php foreach ($rows as $row): ?>
                    <?php
                    $fullname = trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-center align-middle">
                            <span class="inline-block bg-gray-100 text-gray-800 rounded px-2 py-0.5 text-xs"><?php echo htmlspecialchars($row['username']); ?></span>
                        </td>
                        <td class="px-3 py-2 text-center align-middle"><?php echo htmlspecialchars($fullname); ?></td>
                        <td class="px-3 py-2 text-center align-middle"><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td class="px-3 py-2 text-center align-middle"><?php echo htmlspecialchars($row['sex']); ?></td>
                        <td class="px-3 py-2 text-center align-middle">
                            <span class="inline-block bg-blue-200 text-blue-900 rounded px-2 py-0.5 text-xs"><?php echo htmlspecialchars($row['course']); ?></span>
                        </td>
                        <td class="px-3 py-2 text-center align-middle">
                            <span class="inline-block bg-blue-50 text-blue-900 rounded px-2 py-0.5 text-xs"><?php echo htmlspecialchars($row['year_level']); ?></span>
                        </td>
                        <td class="px-3 py-2 text-center align-middle">
                            <span class="inline-block bg-green-100 text-green-800 rounded px-2 py-0.5 text-xs"><?php echo htmlspecialchars($row['scholarship_type']); ?></span>
                        </td>
                        <td class="px-3 py-2 text-center align-middle"><?php echo htmlspecialchars($row['password_plain'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    // Add loading state for better UX
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.querySelector('table');
        if (table) {
            table.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => {
                table.classList.remove('opacity-0');
                table.classList.add('opacity-100');
            }, 100);
        }
    });
</script>
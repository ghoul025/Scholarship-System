<?php
session_start();
require '../config.php';
require '../includes/school_years.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$years = listSchoolYears();
$current = getCurrentSchoolYear();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage School Years</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-gray-50 text-gray-800">

<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-6">
    <!-- Flash Messages -->
    <?php if(isset($_SESSION['sy_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded relative">
            <?= $_SESSION['sy_message']; unset($_SESSION['sy_message']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['sy_error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded relative">
            <?= $_SESSION['sy_error']; unset($_SESSION['sy_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Add School Year Form -->
    <div class="bg-white shadow-lg rounded-lg p-4">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold text-lg"><i class="bi bi-plus-circle"></i> Add School Year</h2>
            <a href="manage_scholars.php" class="text-blue-600 hover:underline text-sm flex items-center"><i class="bi bi-people me-1"></i> Back to Scholars</a>
        </div>
        <form method="POST" action="actions/school_years.php" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <input type="hidden" name="action" value="create">
            <input type="text" name="label" placeholder="Label e.g. 2024-2025" class="col-span-2 p-2 border rounded shadow-sm" required>
            <input type="date" name="start_date" class="p-2 border rounded shadow-sm">
            <input type="date" name="end_date" class="p-2 border rounded shadow-sm">
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="is_current" class="rounded border-gray-300 text-blue-600">
                <span class="text-sm text-gray-700">Current</span>
            </label>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm col-span-1"><i class="bi bi-plus-circle"></i> Add</button>
        </form>
    </div>

    <!-- School Years Table -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-blue-600 text-white px-4 py-2 font-semibold flex justify-between items-center">
            <span><i class="bi bi-list-ul"></i> School Years</span>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold tracking-wide sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left">Label</th>
                        <th class="px-4 py-2 text-left">Start</th>
                        <th class="px-4 py-2 text-left">End</th>
                        <th class="px-4 py-2 text-left">Current</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($years as $y): ?>
                    <tr class="<?= $y['is_current'] ? 'bg-green-50' : 'hover:bg-blue-50 transition-colors' ?>">
                        <td class="px-4 py-2"><?= htmlspecialchars($y['label']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($y['start_date']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($y['end_date']) ?></td>
                        <td class="px-4 py-2">
                            <?php if($y['is_current']): ?>
                                <span class="bg-green-200 text-green-800 text-xs font-medium px-2 py-1 rounded-full">Current</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2 flex flex-wrap justify-center gap-1">
                            <!-- Set Current -->
                            <?php if(!$y['is_current']): ?>
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="id" value="<?= $y['id'] ?>">
                                    <input type="hidden" name="action" value="set_current">
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs">Set Current</button>
                                </form>
                            <?php endif; ?>
                            <!-- Edit -->
                            <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs" 
                                    onclick="openEditModal('editSY<?= $y['id'] ?>')">Edit</button>
                            <!-- Delete -->
                            <form method="POST" class="inline-block" onsubmit="return confirm('Delete this school year?');">
                                <input type="hidden" name="id" value="<?= $y['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">Delete</button>
                            </form>

                            <!-- Edit Modal -->
                            <div id="editSY<?= $y['id'] ?>" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
                                <div class="bg-white rounded-lg shadow-lg w-96">
                                    <form method="POST" action="actions/school_years.php" class="p-4">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $y['id'] ?>">
                                        <h3 class="text-lg font-semibold mb-3">Edit School Year</h3>
                                        <input type="text" name="label" value="<?= htmlspecialchars($y['label']) ?>" class="mb-2 w-full p-2 border rounded" required>
                                        <input type="date" name="start_date" value="<?= htmlspecialchars($y['start_date']) ?>" class="mb-2 w-full p-2 border rounded">
                                        <input type="date" name="end_date" value="<?= htmlspecialchars($y['end_date']) ?>" class="mb-2 w-full p-2 border rounded">
                                        <label class="flex items-center space-x-2 mb-3">
                                            <input type="checkbox" name="is_current" <?= $y['is_current'] ? 'checked' : '' ?> class="rounded border-gray-300">
                                            <span class="text-sm text-gray-700">Current</span>
                                        </label>
                                        <div class="flex justify-end space-x-2">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save</button>
                                            <button type="button" onclick="closeEditModal('editSY<?= $y['id'] ?>')" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($years)): ?>
                        <tr><td colspan="5" class="text-center py-6 text-gray-500">No school years found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
function openEditModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeEditModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>

</body>
</html>

<?php
session_start();
require '../config.php';
// Only main admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$stmt = $conn->prepare("SELECT main_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['main_admin'] != 1) {
    echo "Access denied. Only the main admin can access this dashboard.";
    exit;
}
$total_scholars = $conn->query("SELECT COUNT(*) FROM scholars")->fetchColumn();
$total_admins = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
// Updated to use 'documents' table
$total_documents = $conn->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$pending_docs = $conn->query("SELECT COUNT(*) FROM documents WHERE status = 'pending'")->fetchColumn();
// Documents grouped by status for chart
$status_stmt = $conn->query("SELECT status, COUNT(*) as count FROM documents GROUP BY status");
$status_counts = [];
while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
    $status_counts[$row['status']] = (int)$row['count'];
}
$chart_labels = json_encode(array_keys($status_counts));
$chart_data = json_encode(array_values($status_counts));
$pending_percentage = $total_documents ? round(($pending_docs / $total_documents) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Main Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- Chart.js for analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="bg-gray-100 min-h-screen font-sans">

<?php include __DIR__ . '/includes/main_navbar.php'; ?>


<main class="pt-10 max-w-7xl mx-auto p-4">
    <!-- Main Admin Download Credentials Button -->
    <div class="mb-8 flex gap-2 justify-end">
        <a href="actions/download_all_credentials.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
            <i class="bi bi-download mr-2"></i>Download All Credentials (ZIP)
        </a>
    </div>

    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-700 to-blue-500 text-white text-center py-8 rounded-2xl shadow-md mb-6">
        <h1 class="text-2xl md:text-3xl font-bold tracking-wide mb-1">Main Admin Dashboard</h1>
        <p class="text-sm md:text-base font-medium text-blue-100">Exclusive access to sub-admin and system management features</p>
    </header>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white shadow-md rounded-2xl p-6 text-center hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-center mb-3">
                <i class="bi bi-people text-4xl text-blue-600"></i>
            </div>
            <p class="font-semibold text-gray-600 text-sm md:text-base">Scholars</p>
            <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1"><?= $total_scholars ?></p>
        </div>
        <div class="bg-white shadow-md rounded-2xl p-6 text-center hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-center mb-3">
                <i class="bi bi-person-gear text-4xl text-green-600"></i>
            </div>
            <p class="font-semibold text-gray-600 text-sm md:text-base">Admins</p>
            <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1"><?= $total_admins ?></p>
        </div>
        <div class="bg-white shadow-md rounded-2xl p-6 text-center hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-center mb-3">
                <i class="bi bi-file-earmark-text text-4xl text-indigo-600"></i>
            </div>
            <p class="font-semibold text-gray-600 text-sm md:text-base">Documents</p>
            <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1"><?= $total_documents ?></p>
        </div>
        <div class="bg-white shadow-md rounded-2xl p-6 text-center hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-center mb-3">
                <i class="bi bi-hourglass-split text-4xl text-yellow-500"></i>
            </div>
            <p class="font-semibold text-gray-600 text-sm md:text-base">Pending</p>
            <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1"><?= $pending_docs ?></p>
        </div>
    </div>

    <!-- Analytics Section -->
    <section class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-md">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Documents by Status</h2>
            <div class="flex flex-col md:flex-row items-center md:items-start md:space-x-6">
                <div class="w-full md:w-1/2">
                    <canvas id="statusChart" aria-label="Documents by status chart" role="img"></canvas>
                </div>
                <div class="w-full md:w-1/2 mt-4 md:mt-0">
                    <p class="text-sm text-gray-600 mb-2">Status breakdown</p>
                    <ul class="space-y-2">
                        <?php foreach ($status_counts as $status => $count): ?>
                        <li class="flex items-center justify-between">
                            <span class="text-gray-700"><?= htmlspecialchars($status) ?></span>
                            <span class="font-semibold text-gray-900"><?= $count ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-md">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">Pending Documents</h3>
            <p class="text-sm text-gray-600 mb-4"><?= $pending_docs ?> pending out of <?= $total_documents ?> total</p>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="h-4 bg-yellow-500" style="width: <?= $pending_percentage ?>%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2"><?= $pending_percentage ?>% of documents are pending</p>
        </div>
    </section>

</main>

<footer class="text-center py-6 text-gray-500 mt-12">
    &copy; <?= date('Y'); ?> Scholarship Management System. All rights reserved.
</footer>

<script>
// Render documents status chart
document.addEventListener('DOMContentLoaded', function () {
    const labels = <?= $chart_labels ?: '[]' ?>;
    const data = <?= $chart_data ?: '[]' ?>;
    const ctxEl = document.getElementById('statusChart');
    if (!ctxEl) return;
    const ctx = ctxEl.getContext('2d');
    const colors = ['#4f46e5','#06b6d4','#ef4444','#10b981','#f59e0b','#8b5cf6','#7c3aed'];
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

</body>
</html>

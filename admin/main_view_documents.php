<?php
session_start();
require '../config.php';

// Only main admin can access
$user_sql = "SELECT main_admin FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !$user || $user['main_admin'] != 1) {
    echo "Access denied. Only the main admin can access this page.";
    exit;
}

// Fetch all documents for display
// Allow optional status filter via querystring (approved, rejected, pending)
$allowed_status = ['approved','rejected','pending'];
$filter_status = isset($_GET['status']) && in_array($_GET['status'], $allowed_status) ? $_GET['status'] : null;

// Updated to use 'documents' table
$base_sql = "SELECT d.id, CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS full_name, d.document_type, d.file_path, d.status, d.uploaded_at
    FROM documents d
    JOIN scholars s ON d.scholar_id = s.id";
$order_by = " ORDER BY d.uploaded_at DESC";
if ($filter_status) {
    $sql = $base_sql . " WHERE d.status = :status" . $order_by;
    $stmt = $conn->prepare($sql);
    $stmt->execute(['status' => $filter_status]);
} else {
    $sql = $base_sql . $order_by;
    $stmt = $conn->query($sql);
}
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary counts and status grouping for analytics
// Updated analytics queries to use 'documents' table
$total_documents = $conn->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$pending_docs = $conn->query("SELECT COUNT(*) FROM documents WHERE status = 'pending'")->fetchColumn();
$status_stmt = $conn->query("SELECT status, COUNT(*) as count FROM documents GROUP BY status");
$status_counts = [];
while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
    $status_counts[$row['status']] = (int)$row['count'];
}
$chart_labels = json_encode(array_keys($status_counts));
$chart_data = json_encode(array_values($status_counts));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Documents (Main Admin)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">

    <!-- Navbar -->
    <?php include __DIR__ . '/includes/main_navbar.php'; ?>

    <main class="pt-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold text-blue-700 mb-1">Documents List</h1>
            <p class="text-gray-600 text-sm">Only the <b>Main Admin</b> can view uploaded documents.</p>
        </div>

        <!-- Analytics Summary + Download Credentials Button -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl shadow-md text-center">
                <div class="text-sm text-gray-500">Total Documents</div>
                <div id="totalDocuments" class="text-2xl font-bold text-gray-900"><?= $total_documents ?></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md text-center">
                <div class="text-sm text-gray-500">Pending</div>
                <div class="text-2xl font-bold text-yellow-600"><?= $pending_docs ?></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md text-center">
                <div class="text-sm text-gray-500">Filtered</div>
                <div class="text-2xl font-bold text-blue-700"><?= $filter_status ? htmlspecialchars($filter_status) : 'All' ?></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md text-center">
                <div class="text-sm text-gray-500">Actions</div>
                <div class="mt-1">
                    <a href="#" id="docsResetBtn" class="text-sm text-blue-600 underline">Reset Filter</a>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md text-center flex flex-col justify-center items-center">
                <div class="text-sm text-gray-500 mb-1">Bulk Download</div>
                <a href="actions/download_all_credentials.php" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-full transition">
                    <i class="bi bi-download"></i> Download Credentials (ZIP)
                </a>
            </div>
        </div>

        <!-- Documents Analytics -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <div class="md:flex md:items-start md:space-x-6">
                <div class="md:w-1/3 h-48">
                    <canvas id="docsStatusChart" aria-label="Documents status chart" role="img"></canvas>
                </div>
                <div class="md:flex-1 mt-4 md:mt-0">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Status Breakdown</h3>
                    <ul id="statusList" class="space-y-2">
                        <?php foreach ($status_counts as $status => $count): ?>
                        <li class="flex items-center justify-between">
                            <a href="?status=<?= urlencode($status) ?>" class="text-sm text-blue-600 hover:underline"><?= htmlspecialchars($status) ?></a>
                            <span class="font-semibold"><?= $count ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Documents Table Card -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-6 py-4 font-semibold text-lg">
                <i class="bi bi-folder-fill mr-2"></i> Documents List
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-left divide-y divide-gray-200">
                    <thead class="bg-blue-50 text-blue-700 font-semibold">
                        <tr>
                            <th class="px-4 py-3">Scholar</th>
                            <th class="px-4 py-3">Document Type</th>
                            <th class="px-4 py-3">File</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Uploaded At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($documents as $doc): ?>
                            <tr class="hover:bg-blue-50 transition-all">
                                <td class="px-4 py-2"><?= htmlspecialchars($doc['full_name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($doc['document_type']) ?></td>
                                <td class="px-4 py-2">
                                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="inline-flex items-center gap-1 bg-blue-500 text-white text-sm font-semibold px-3 py-1 rounded-full hover:bg-blue-600 transition-all">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                                <td class="px-4 py-2">
                                    <?php if ($doc['status'] == 'Approved'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">Approved</span>
                                    <?php elseif ($doc['status'] == 'Rejected'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">Rejected</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2"><?= htmlspecialchars($doc['uploaded_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="text-center text-gray-500 text-sm mt-10 mb-6">
        &copy; <?= date('Y'); ?> Scholarship Management System. All rights reserved.
    </footer>

        <script>
        (function(){
            const defaultLabels = <?= $chart_labels ?: '[]' ?>;
            const defaultData = <?= $chart_data ?: '[]' ?>;
            const el = document.getElementById('docsStatusChart');
            if (!el) return;
            const ctx = el.getContext('2d');
            const colors = ['#4f46e5','#06b6d4','#ef4444','#10b981','#f59e0b','#8b5cf6'];
            window.docsChart = new Chart(ctx, {
                type: 'doughnut',
                data: { labels: defaultLabels, datasets: [{ data: defaultData, backgroundColor: colors.slice(0, defaultData.length) }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });

            async function fetchDocs(params) {
                const url = 'ajax_fetch_documents.php' + (params ? ('?' + params) : '');
                try {
                    const res = await fetch(url, { credentials: 'same-origin' });
                    if (!res.ok) throw new Error('Network response not ok');
                    const j = await res.json();
                    // update table
                    const tbody = document.querySelector('table tbody');
                    if (tbody) tbody.innerHTML = j.rows;
                    // update total
                    const totalEl = document.getElementById('totalDocuments');
                    if (totalEl) totalEl.textContent = j.total;
                    // update status list
                    const list = document.getElementById('statusList');
                    if (list) list.innerHTML = j.status_html;
                    // update chart
                    if (window.docsChart) {
                        window.docsChart.data.labels = j.chart_labels || [];
                        window.docsChart.data.datasets[0].data = j.chart_data || [];
                        window.docsChart.update();
                    }
                } catch (err) { console.error('Fetch error', err); }
            }

            // Hook status links
            document.querySelectorAll('#statusList a').forEach(a => {
                a.addEventListener('click', function(e){
                    e.preventDefault();
                    const url = new URL(a.href, window.location.origin);
                    const params = url.searchParams.toString();
                    fetchDocs(params);
                });
            });

            const resetBtn = document.getElementById('docsResetBtn');
            if (resetBtn) resetBtn.addEventListener('click', function(e){ e.preventDefault(); fetchDocs(''); });

        })();
        </script>

    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity:1; transform: translateY(0);} }
        .animate-fadeIn { animation: fadeIn 0.6s ease forwards; }
    </style>

</body>
</html>

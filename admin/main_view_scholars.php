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

// --- Filters (GET) ---
$q = trim($_GET['q'] ?? '');
$filter_type = $_GET['scholarship_type'] ?? '';
$filter_sex = $_GET['sex'] ?? '';
$filter_year = $_GET['year_level'] ?? '';

// Build WHERE clauses and params for prepared statements
$where = [];
$params = [];
if ($q !== '') {
    $where[] = "(u.username LIKE ? OR s.first_name LIKE ? OR s.middle_name LIKE ? OR s.last_name LIKE ? OR CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) LIKE ? )";
    $like = "%{$q}%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($filter_type !== '') { $where[] = "s.scholarship_type = ?"; $params[] = $filter_type; }
if ($filter_sex !== '') { $where[] = "s.sex = ?"; $params[] = $filter_sex; }
if ($filter_year !== '') { $where[] = "s.year_level = ?"; $params[] = $filter_year; }

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Fetch filtered scholars
$sql = "SELECT s.id, u.username, CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS full_name, s.first_name, s.middle_name, s.last_name, s.phone, s.sex, s.units, s.tuition_fee, s.course, s.year_level, s.scholarship_type
    FROM scholars s
    JOIN users u ON s.user_id = u.id
    {$where_sql}
    ORDER BY s.last_name ASC, s.first_name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Analytics computed from the filtered set
$count_sql = "SELECT COUNT(*) FROM scholars s JOIN users u ON s.user_id = u.id {$where_sql}";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_scholars = (int)$count_stmt->fetchColumn();

$type_sql = "SELECT scholarship_type, COUNT(*) as count FROM scholars s JOIN users u ON s.user_id = u.id {$where_sql} GROUP BY scholarship_type";
$type_stmt = $conn->prepare($type_sql);
$type_stmt->execute($params);
$type_counts = [];
while ($r = $type_stmt->fetch(PDO::FETCH_ASSOC)) { $type_counts[$r['scholarship_type']] = (int)$r['count']; }

$sex_sql = "SELECT sex, COUNT(*) as count FROM scholars s JOIN users u ON s.user_id = u.id {$where_sql} GROUP BY sex";
$sex_stmt = $conn->prepare($sex_sql);
$sex_stmt->execute($params);
$sex_counts = [];
while ($r = $sex_stmt->fetch(PDO::FETCH_ASSOC)) { $sex_counts[$r['sex']] = (int)$r['count']; }

$type_labels = json_encode(array_keys($type_counts));
$type_data = json_encode(array_values($type_counts));
$sex_labels = json_encode(array_keys($sex_counts));
$sex_data = json_encode(array_values($sex_counts));

// For filter dropdown options (all possible values)
$all_types = $conn->query("SELECT DISTINCT scholarship_type FROM scholars WHERE scholarship_type IS NOT NULL AND scholarship_type <> '' ORDER BY scholarship_type")->fetchAll(PDO::FETCH_COLUMN);
$all_years = $conn->query("SELECT DISTINCT year_level FROM scholars WHERE year_level IS NOT NULL AND year_level <> '' ORDER BY year_level")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Scholars (Main Admin)</title>
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
            <h1 class="text-3xl font-extrabold text-blue-700 mb-1">Scholars List</h1>
            <p class="text-gray-600 text-sm">Only the <b>Main Admin</b> can view scholar details.</p>
        </div>

        <!-- Filters -->
        <form id="scholarFiltersForm" method="get" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-sm text-gray-600">Search</label>
                <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Name or username" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm px-3 py-2" />
            </div>
            <div>
                <label class="text-sm text-gray-600">Scholarship Type</label>
                <select name="scholarship_type" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm px-3 py-2">
                    <option value="">All types</option>
                    <?php foreach ($all_types as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>" <?= $filter_type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-sm text-gray-600">Sex</label>
                <select name="sex" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm px-3 py-2">
                    <option value="">All</option>
                    <option value="Male" <?= $filter_sex === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $filter_sex === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= $filter_sex === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div>
                <label class="text-sm text-gray-600">Year Level</label>
                <select name="year_level" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm px-3 py-2">
                    <option value="">All years</option>
                    <?php foreach ($all_years as $y): ?>
                        <option value="<?= htmlspecialchars($y) ?>" <?= $filter_year === $y ? 'selected' : '' ?>><?= htmlspecialchars($y) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-4 flex gap-2 mt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Apply</button>
                <a href="#" id="scholarClearBtn" class="px-4 py-2 border rounded-md text-sm text-gray-700">Clear</a>
            </div>
        </form>

        <!-- Scholars Analytics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl shadow-md text-center">
                    <div class="text-sm text-gray-500">Total Scholars</div>
                    <div id="totalScholars" class="text-2xl font-bold text-gray-900"><?= $total_scholars ?></div>
                </div>
            <div class="bg-white p-4 rounded-xl shadow-md">
                <h4 class="text-sm text-gray-600 mb-2">Scholarship Types</h4>
                <div class="h-36"><canvas id="typeChart"></canvas></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-md">
                <h4 class="text-sm text-gray-600 mb-2">Sex Distribution</h4>
                <div class="h-36"><canvas id="sexChart"></canvas></div>
            </div>
        </div>

        <!-- Scholars Table Card -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-6 py-4 font-semibold text-lg">
                <i class="bi bi-people-fill mr-2"></i> Scholars List
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-left divide-y divide-gray-200">
                    <thead class="bg-blue-50 text-blue-700 font-semibold">
                        <tr>
                            <th class="px-4 py-3">Username</th>
                            <th class="px-4 py-3">Full Name</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Sex</th>
                            <th class="px-4 py-3">Units</th>
                            <th class="px-4 py-3">Tuition Fee</th>
                            <th class="px-4 py-3">Course</th>
                            <th class="px-4 py-3">Year Level</th>
                            <th class="px-4 py-3">Scholarship Type</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($scholars as $scholar):
                            $first = $scholar['first_name'] ?? '';
                            $last = $scholar['last_name'] ?? '';
                            $initials = strtoupper(substr(($first), 0, 1) . substr(($last), 0, 1));
                            $tuition = is_numeric($scholar['tuition_fee']) ? number_format($scholar['tuition_fee'], 2) : htmlspecialchars($scholar['tuition_fee']);
                            $sch_type = $scholar['scholarship_type'] ?? '';
                            $stype_low = strtolower($sch_type);
                            $badgeClass = 'bg-blue-50 text-blue-800';
                            if (strpos($stype_low, 'full') !== false) { $badgeClass = 'bg-green-100 text-green-800'; }
                            else if (strpos($stype_low, 'partial') !== false) { $badgeClass = 'bg-yellow-100 text-yellow-800'; }
                            else if (strpos($stype_low, 'tes') !== false) { $badgeClass = 'bg-indigo-100 text-indigo-800'; }
                        ?>
                            <tr class="hover:bg-blue-50 transition-all">
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center font-semibold"><?= htmlspecialchars($initials) ?></div>
                                        <div>
                                            <div class="font-medium text-gray-800"><?= htmlspecialchars($scholar['username']) ?></div>
                                            <div class="text-xs text-gray-500">ID: <?= htmlspecialchars($scholar['id']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-800"><?= htmlspecialchars(trim(($first) . ' ' . ($scholar['middle_name'] ?? '') . ' ' . ($last))) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($scholar['course']) ?></div>
                                </td>
                                <td class="px-4 py-3"><div class="text-sm text-gray-700"><?= htmlspecialchars($scholar['phone']) ?></div></td>
                                <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><?= htmlspecialchars($scholar['sex']) ?></span></td>
                                <td class="px-4 py-3"><div class="text-sm text-gray-700"><?= htmlspecialchars($scholar['units']) ?></div></td>
                                <td class="px-4 py-3"><div class="text-sm text-gray-800">₱ <?= $tuition ?></div></td>
                                <td class="px-4 py-3"><div class="text-sm text-gray-700"><?= htmlspecialchars($scholar['course']) ?></div></td>
                                <td class="px-4 py-3"><div class="text-sm text-gray-700"><?= htmlspecialchars($scholar['year_level']) ?></div></td>
                                <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>"><?= htmlspecialchars($sch_type) ?></span></td>
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
        const typeCtx = document.getElementById('typeChart')?.getContext('2d');
        const sexCtx = document.getElementById('sexChart')?.getContext('2d');
        const defaultTypeLabels = <?= $type_labels ?: '[]' ?>;
        const defaultTypeData = <?= $type_data ?: '[]' ?>;
        const defaultSexLabels = <?= $sex_labels ?: '[]' ?>;
        const defaultSexData = <?= $sex_data ?: '[]' ?>;

        function createChart(ctx, cfg){
            if (!ctx) return null;
            return new Chart(ctx, cfg);
        }

        window.typeChartObj = createChart(typeCtx, {
            type: 'doughnut',
            data: { labels: defaultTypeLabels, datasets: [{ data: defaultTypeData, backgroundColor: ['#4f46e5','#06b6d4','#10b981','#f59e0b'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        window.sexChartObj = createChart(sexCtx, {
            type: 'pie',
            data: { labels: defaultSexLabels, datasets: [{ data: defaultSexData, backgroundColor: ['#db0e97ff','#1f10ecff','#94a3b8'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        function updateChart(chart, labels, data) {
            if (!chart) return;
            chart.data.labels = labels;
            chart.data.datasets[0].data = data;
            chart.update();
        }

        async function fetchAndApply(params) {
            const url = 'ajax_fetch_scholars.php?' + params;
            try {
                const res = await fetch(url, { credentials: 'same-origin' });
                if (!res.ok) throw new Error('Network response was not ok');
                const j = await res.json();
                // Update table rows
                const tbody = document.querySelector('table tbody');
                if (tbody) tbody.innerHTML = j.rows;
                // Update total
                const totalEl = document.getElementById('totalScholars');
                if (totalEl) totalEl.textContent = j.total;
                // Update charts
                updateChart(window.typeChartObj, j.type_labels || [], j.type_data || []);
                updateChart(window.sexChartObj, j.sex_labels || [], j.sex_data || []);
            } catch (err) {
                console.error('Fetch error', err);
            }
        }

        const form = document.getElementById('scholarFiltersForm');
        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                const params = new URLSearchParams(new FormData(form)).toString();
                fetchAndApply(params);
            });
        }

        const clearBtn = document.getElementById('scholarClearBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e){
                e.preventDefault();
                form.reset();
                fetchAndApply('');
            });
        }

    })();
    </script>

    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity:1; transform: translateY(0);} }
        .animate-fadeIn { animation: fadeIn 0.6s ease forwards; }
    </style>

</body>
</html>

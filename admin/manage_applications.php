<?php
session_start();
require '../config.php';

// Restrict to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle delete TES application
if (isset($_POST['delete_tes'])) {
    $delete_id = trim($_POST['delete_tes']);
    $del_stmt = $conn->prepare("DELETE FROM tes_applicants WHERE student_id = ?");
    $del_stmt->execute([$delete_id]);
    $_SESSION['batch_message'] = "TES application deleted successfully.";
    // Modified redirect to include tab parameter
    $active_tab = isset($_POST['active_tab']) ? $_POST['active_tab'] : 'scholar';
    header("Location: manage_applications.php?tab=$active_tab");
    exit;
}

// Fetch scholarship applications
$sql = "SELECT * FROM scholar_applications ORDER BY last_name ASC, first_name ASC";
$stmt = $conn->query($sql);
$applications = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Format batch
foreach ($applications as &$app) {
    $app['batch_formatted'] = !empty($app['batch']) ? rtrim(rtrim(number_format((float)$app['batch'], 2, '.', ''), '0'), '.') : '—';
}

// Fetch TES applications
$sql_tes = "SELECT * FROM tes_applicants ORDER BY last_name ASC, given_name ASC";
$stmt_tes = $conn->query($sql_tes);
$tes_applications = $stmt_tes ? $stmt_tes->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Applications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-gray-50 text-gray-800">
    <?php include __DIR__ . '/includes/navbar.php'; ?>
   
    <main class="max-w-7xl mx-auto py-5 px-4">
        <?php if (isset($_SESSION['batch_message'])): ?>
            <div class="mb-4 p-3 rounded bg-green-50 border-l-4 border-green-600 text-green-800"><?= htmlspecialchars($_SESSION['batch_message']); unset($_SESSION['batch_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['batch_error'])): ?>
            <div class="mb-4 p-3 rounded bg-red-50 border-l-4 border-red-600 text-red-800"><?= htmlspecialchars($_SESSION['batch_error']); unset($_SESSION['batch_error']); ?></div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <nav class="flex border-b border-gray-200 mb-4">
            <button class="tab-btn px-4 py-2 text-blue-600 border-b-2 border-blue-600 font-medium" data-tab="scholar">Continuing Grantees</button>
            <button class="tab-btn px-4 py-2 text-gray-600 font-medium" data-tab="tes">Scholar Applications</button>
        </nav>

        <!-- Scholarship Tab Content -->
        <div id="scholar-tab" class="tab-content">
            <!-- Scholarship Table -->
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-person-check-fill text-2xl text-blue-700"></i>
                        <div>
                            <div class="text-lg font-semibold text-gray-900">Pending Scholar Grantees</div>
                            <div class="text-sm text-gray-500">Review and approve/reject registrations</div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" id="applications-table">
                        <thead class="bg-blue-50 sticky top-0 z-0">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-blue-900">Full Name</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Username</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Phone</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Sex</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Units</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Tuition Fee</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Course</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Year Level</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Type</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Batch</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100" id="applications-table-body">
                            <?php foreach ($applications as $app): ?>
                                <?php $full = trim($app['first_name'] . ' ' . ($app['middle_name'] ? $app['middle_name'] . ' ' : '') . $app['last_name']); ?>
                                <tr class="table-row-hover">
                                    <td class="px-3 py-2 align-middle font-semibold text-gray-900"><?= htmlspecialchars($full) ?></td>
                                    <td class="px-3 py-2 text-center align-middle"><?= htmlspecialchars($app['username']) ?></td>
                                    <td class="px-3 py-2 text-center align-middle"><?= htmlspecialchars($app['phone']) ?></td>
                                    <td class="px-3 py-2 text-center align-middle"><?= htmlspecialchars($app['sex']) ?></td>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <span class="inline-block bg-blue-100 text-blue-800 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($app['units']) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <span class="inline-block bg-gray-100 text-gray-800 rounded px-2 py-0.5 text-xs">₱<?= number_format($app['tuition_fee'], 2) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <span class="inline-block bg-blue-200 text-blue-900 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($app['course']) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <span class="inline-block bg-blue-50 text-blue-900 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($app['year_level']) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <span class="inline-block bg-green-100 text-green-800 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($app['scholarship_type']) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <span class="inline-block bg-yellow-100 text-yellow-800 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($app['batch_formatted']) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-center align-middle row-action">
                                        <div class="flex items-center justify-center gap-2">
                                            <form method="POST" action="actions/approve_application.php" data-action="approve" data-app-id="<?= $app['id'] ?>" class="inline-block m-0 p-0 ajax-action-form">
    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" class="csrf-token-input">
    <input type="hidden" name="ajax" value="1">
    <button type="button" class="approve-btn inline-flex items-center justify-center border border-green-200 text-green-700 hover:bg-green-50 rounded px-2 py-1 text-xs" title="Approve">
        <i class="bi bi-check-circle"></i>
    </button>
</form>
                                            <form method="POST" action="actions/reject_application.php" data-action="reject" data-app-id="<?= $app['id'] ?>" class="inline-block m-0 p-0 ajax-action-form">
                                                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" class="csrf-token-input">
                                                <input type="hidden" name="ajax" value="1">
                                                <button type="button" class="reject-btn inline-flex items-center justify-center border border-red-200 text-red-700 hover:bg-red-50 rounded px-2 py-1 text-xs" title="Reject">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- TES Tab Content -->
        <div id="tes-tab" class="tab-content hidden">
            <!-- TES Table -->
            <section class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-person-check-fill text-2xl text-blue-700"></i>
                        <div>
                            <div class="text-lg font-semibold text-gray-900"> Applicants</div>
                            <div class="text-sm text-gray-500">Manage applications</div>
                        </div>
                    </div>
                    <!-- Export Button with Checkboxes -->
                    <div class="mb-4 flex justify-end">
                        <form method="POST" action="actions/export_tes.php" id="export-tes-form">
                            <button type="submit" id="export-tes-btn" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 flex items-center gap-2 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                                <i class="bi bi-file-earmark-excel"></i> Export Selected to Excel
                            </button>
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-blue-50 sticky top-0 z-0">
                            <tr>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">
                                    <input type="checkbox" id="select-all-tes" class="rounded">
                                </th>
                                <th class="px-3 py-2 text-left font-medium text-blue-900">Full Name</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Student ID</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Program</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Year Level</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Street & Barangay</th>
                                <th class="px-3 py-2 text-center font-medium text-blue-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php foreach ($tes_applications as $app): ?>
                                <?php $full = trim($app['last_name'] . ', ' . $app['given_name'] . ($app['extension_name'] ? ' ' . $app['extension_name'] : '') . ' ' . $app['middle_name']); ?>
                                <tr class="table-row-hover">
                                    <td class="px-3 py-2 text-center align-middle">
                                        <input type="checkbox" name="tes_ids[]" form="export-tes-form" value="<?= htmlspecialchars($app['student_id']) ?>" class="tes-checkbox rounded">
                                    </td>
                                    <td class="px-3 py-2 align-middle font-semibold text-gray-900"><?= htmlspecialchars($full) ?></td>
                                    <td class="px-3 py-2 text-center align-middle"><?= htmlspecialchars($app['student_id']) ?></td>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <span class="inline-block bg-blue-200 text-blue-900 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($app['complete_program_name']) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-center align-middle">
                                        <span class="inline-block bg-blue-50 text-blue-900 rounded px-2 py-0.5 text-xs"><?= htmlspecialchars($app['year_level']) ?></span>
                                    </td>
                                    <td class="px-3 py-2 text-center align-middle"><?= htmlspecialchars($app['street_barangay']) ?></td>
                                    <td class="px-3 py-2 text-center align-middle row-action">
                                        <form method="POST" action="" onsubmit="return confirm('Delete this TES application?');" class="inline-block m-0 p-0">
                                            <input type="hidden" name="delete_tes" value="<?= htmlspecialchars($app['student_id']) ?>">
                                            <!-- Added hidden input for active tab -->
                                            <input type="hidden" name="active_tab" value="tes">
                                            <button type="submit" class="inline-flex items-center justify-center border border-red-200 text-red-700 hover:bg-red-50 rounded px-2 py-1 text-xs" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script>
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        // Function to activate a specific tab
        function activateTab(tabName) {
            tabBtns.forEach(b => {
                b.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
                b.classList.add('text-gray-600');
            });
            tabContents.forEach(c => c.classList.add('hidden'));

            const targetBtn = Array.from(tabBtns).find(b => b.dataset.tab === tabName);
            const targetContent = document.getElementById(tabName + '-tab');

            if (targetBtn && targetContent) {
                targetBtn.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
                targetBtn.classList.remove('text-gray-600');
                targetContent.classList.remove('hidden');
            }
        }

        // Check URL for tab parameter on page load
        window.addEventListener('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'scholar';
            activateTab(activeTab);
        });

        // Tab button click handler
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                activateTab(btn.dataset.tab);
            });
        });

        // Checkbox functionality for TES export
        const selectAllCheckbox = document.getElementById('select-all-tes');
        const tesCheckboxes = document.querySelectorAll('.tes-checkbox');
        const exportBtn = document.getElementById('export-tes-btn');

        function updateExportButton() {
            const anyChecked = Array.from(tesCheckboxes).some(cb => cb.checked);
            exportBtn.disabled = !anyChecked;
        }

        selectAllCheckbox.addEventListener('change', () => {
            tesCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateExportButton();
        });

        tesCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                selectAllCheckbox.checked = Array.from(tesCheckboxes).every(cb => cb.checked);
                updateExportButton();
            });
        });
    </script>
    <script>
        // Enhanced multi-key sorting with header click toggles.
        // Default scholar hierarchy: batchNum, scholarshipType, yearNum, last, first, sex, course
        // Clicking a header makes that column the primary sort and toggles asc/desc.
        (function() {
            function text(cell) {
                return cell ? cell.textContent.trim().toLowerCase() : '';
            }

            function parseBatch(textVal) {
                if (!textVal) return Number.NEGATIVE_INFINITY;
                var cleaned = (textVal+'').replace(/[^0-9.\-]/g, '');
                var n = parseFloat(cleaned);
                return isNaN(n) ? Number.NEGATIVE_INFINITY : n;
            }

            // Stable merge sort
            function stableSort(arr, cmp) {
                if (arr.length <= 1) return arr.slice();
                var mid = Math.floor(arr.length / 2);
                var left = stableSort(arr.slice(0, mid), cmp);
                var right = stableSort(arr.slice(mid), cmp);
                var res = [];
                var i = 0, j = 0;
                while (i < left.length && j < right.length) {
                    if (cmp(left[i], right[j]) <= 0) res.push(left[i++]); else res.push(right[j++]);
                }
                while (i < left.length) res.push(left[i++]);
                while (j < right.length) res.push(right[j++]);
                return res;
            }

            // Utilities to extract scholar row data
            function scholarRowData(row, idx) {
                var cells = row.children;
                var fullName = text(cells[0]);
                var nameParts = fullName.split(/\s+/).filter(Boolean);
                var first = nameParts[0] || '';
                var last = nameParts[nameParts.length-1] || '';
                var sex = text(cells[3]);
                var course = text(cells[6]);
                var yearNum = parseFloat(text(cells[7])) || Number.NEGATIVE_INFINITY;
                var scholarshipType = text(cells[8]);
                var batchNum = parseBatch(text(cells[9]));
                return { row: row, idx: idx, first: first, last: last, sex: sex, course: course, yearNum: yearNum, scholarshipType: scholarshipType, batchNum: batchNum };
            }

            // Comparator factory based on fields array and direction map
            function makeComparator(fields, directions) {
                return function(a, b) {
                    for (var i = 0; i < fields.length; i++) {
                        var f = fields[i];
                        var dir = directions && directions[f] === 'desc' ? -1 : 1;
                        var av = a[f];
                        var bv = b[f];
                        if (av === bv) continue;
                        // treat undefined/null consistently
                        if (av == null) return -1 * dir;
                        if (bv == null) return 1 * dir;
                        // numeric fields
                        if (typeof av === 'number' || typeof bv === 'number') {
                            var na = Number(av || 0);
                            var nb = Number(bv || 0);
                            if (na === nb) continue;
                            return (na < nb ? -1 : 1) * dir;
                        }
                        // strings
                        var cmp = av.localeCompare(bv);
                        if (cmp !== 0) return cmp * dir;
                    }
                    return a.idx - b.idx;
                };
            }

            // Default scholar hierarchy
            var scholarDefaultFields = ['batchNum', 'scholarshipType', 'yearNum', 'last', 'first', 'sex', 'course'];

            function applyScholarSort(primaryField, direction) {
                try {
                    var tbody = document.getElementById('applications-table-body');
                    if (!tbody) return;
                    var rows = Array.from(tbody.querySelectorAll('tr'));
                    var parsed = rows.map(function(r, i) { return scholarRowData(r, i); });

                    // Build fields array: primary first (if provided), then defaults without duplicates
                    var fields = [];
                    if (primaryField) fields.push(primaryField);
                    scholarDefaultFields.forEach(function(f) { if (fields.indexOf(f) === -1) fields.push(f); });

                    var directions = {};
                    if (direction) directions[primaryField] = direction;

                    var cmp = makeComparator(fields, directions);
                    var sorted = stableSort(parsed, cmp);
                    var frag = document.createDocumentFragment();
                    sorted.forEach(function(p) { frag.appendChild(p.row); });
                    tbody.appendChild(frag);
                } catch (e) {
                    console.error('applyScholarSort failed', e);
                }
            }

            // Initialize: apply default scholar sort (batch first ascending)
            window.addEventListener('load', function() {
                applyScholarSort('batchNum', 'asc');

                // Add clickable headers for scholar table
                try {
                    var scholarTable = document.getElementById('applications-table');
                    if (scholarTable) {
                        var ths = scholarTable.querySelectorAll('thead th');
                        // mapping of column index to field name
                        var colField = {
                            0: 'last', // name column (we'll treat as last+first)
                            3: 'sex',
                            6: 'course',
                            7: 'yearNum',
                            8: 'scholarshipType',
                            9: 'batchNum'
                        };
                        var sortState = {}; // key: field -> 'asc'|'desc'

                        ths.forEach(function(th, idx) {
                            if (!(idx in colField)) return;
                            th.style.cursor = 'pointer';
                            var indicator = document.createElement('span');
                            indicator.className = 'sort-indicator ml-2';
                            indicator.style.fontSize = '0.75em';
                            indicator.textContent = '';
                            th.appendChild(indicator);

                            th.addEventListener('click', function() {
                                var field = colField[idx];
                                var cur = sortState[field] === 'asc' ? 'desc' : 'asc';
                                // reset indicators
                                scholarTable.querySelectorAll('.sort-indicator').forEach(function(sp) { sp.textContent = ''; });
                                indicator.textContent = cur === 'asc' ? '▲' : '▼';
                                sortState[field] = cur;
                                applyScholarSort(field, cur);
                            });
                        });
                    }
                } catch (e) { console.error(e); }

                // TES table: add header clicks and default sort (last,given)
                try {
                    var tesTable = document.querySelector('#tes-tab table');
                    if (tesTable) {
                        var tth = tesTable.querySelectorAll('thead th');
                        // mapping for TES: Full Name col 1 -> last,given ; Program col 3 -> program ; Year col 4 -> year
                        var tesColField = {1: 'name', 3: 'program', 4: 'year'};
                        var tesSortState = {};

                        function tesRowData(row, idx) {
                            var cells = row.children;
                            var full = text(cells[1]);
                            var parts = full.split(/,\s*/);
                            var last = parts[0] || '';
                            var given = parts[1] || '';
                            var program = text(cells[3]);
                            var year = parseFloat(text(cells[4])) || Number.NEGATIVE_INFINITY;
                            return { row: row, idx: idx, last: last, given: given, program: program, year: year };
                        }

                        function applyTesSort(primaryField, dir) {
                            var tbody = tesTable.querySelector('tbody');
                            if (!tbody) return;
                            var rows = Array.from(tbody.querySelectorAll('tr'));
                            var parsed = rows.map(function(r, i) { return tesRowData(r, i); });
                            var fields = [];
                            if (primaryField === 'name') { fields.push('last'); fields.push('given'); }
                            else if (primaryField) fields.push(primaryField);
                            ['last','given','program','year'].forEach(function(f) { if (fields.indexOf(f) === -1) fields.push(f); });
                            var directions = {};
                            if (dir) {
                                if (primaryField === 'name') { directions['last'] = dir; directions['given'] = dir; }
                                else directions[primaryField] = dir;
                            }
                            var cmp = makeComparator(fields, directions);
                            var sorted = stableSort(parsed, cmp);
                            var frag = document.createDocumentFragment();
                            sorted.forEach(function(p) { frag.appendChild(p.row); });
                            tbody.appendChild(frag);
                        }

                        // default TES sort
                        applyTesSort('name', 'asc');

                        tth.forEach(function(th, idx) {
                            if (!(idx in tesColField)) return;
                            th.style.cursor = 'pointer';
                            var indicator = document.createElement('span');
                            indicator.className = 'sort-indicator ml-2';
                            indicator.style.fontSize = '0.75em';
                            indicator.textContent = '';
                            th.appendChild(indicator);

                            th.addEventListener('click', function() {
                                var key = tesColField[idx];
                                var cur = tesSortState[key] === 'asc' ? 'desc' : 'asc';
                                tesTable.querySelectorAll('.sort-indicator').forEach(function(sp) { sp.textContent = ''; });
                                indicator.textContent = cur === 'asc' ? '▲' : '▼';
                                tesSortState[key] = cur;
                                applyTesSort(key, cur);
                            });
                        });
                    }
                } catch (e) { console.error(e); }
            });
        })();
    </script>
    <script>
        // Helper to show temporary messages
        function showMessage(text, type = 'success') {
            const div = document.createElement('div');
            div.className = type === 'success' ? 'mb-4 p-3 rounded bg-green-50 border-l-4 border-green-600 text-green-800' : 'mb-4 p-3 rounded bg-red-50 border-l-4 border-red-600 text-red-800';
            div.textContent = text;
            const main = document.querySelector('main');
            main.prepend(div);
            setTimeout(() => div.remove(), 6000);
        }

        // Compute absolute URL for a possibly-relative action attribute.
        function getAbsoluteAction(action) {
            try {
                // Let the browser resolve relative URLs safely via the URL constructor
                return new URL(action, window.location.href).href;
            } catch (e) {
                // Fallback: prefix with origin + pathname directory
                const origin = window.location.origin;
                const path = window.location.pathname.replace(/\/[^\/]*$/, '/');
                return origin + path + action.replace(/^\/+/, '');
            }
        }

        // Centralized AJAX handler for forms with class .ajax-action-form
        async function submitAjaxForm(form, button) {
            const action = form.getAttribute('action') || '';
            const url = getAbsoluteAction(action);
            const fd = new FormData(form);

            // Ensure X-Requested-With is present for server-side AJAX detection and request.json Accept
            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            };

            try {
                button.disabled = true;
                const res = await fetch(url, {
                    method: 'POST',
                    body: fd,
                    headers: headers,
                    credentials: 'same-origin',
                    redirect: 'manual' // avoid following server redirects that can cause page navigation
                });

                // If server returns redirect (e.g., non-AJAX fallback), don't let browser navigate; surface an informative message
                if (res.status >= 300 && res.status < 400) {
                    showMessage('Server attempted to redirect. The request should be handled via AJAX — please try again or contact the admin.', 'error');
                    return;
                }

                const contentType = res.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    // Try to read text for debugging, but avoid navigating the page
                    const text = await res.text();
                    // Attempt to extract a short message from returned HTML/plain text
                    const preview = text.replace(/<[^>]+>/g, '').trim().slice(0, 500);
                    throw new Error('Unexpected server response: ' + (preview || res.statusText));
                }

                const data = await res.json();
                if (!data) throw new Error('Empty JSON response from server.');

                if (!data.success) {
                    if (data.csrf_token) updateCsrfTokens(data.csrf_token);
                    throw new Error(data.message || 'Operation failed');
                }

                // Success: remove row from table and show message
                const row = form.closest('tr');
                if (row) row.remove();
                if (data.message) showMessage(data.message, 'success');
                if (data.password) showMessage('Temporary password: ' + data.password, 'success');
                if (data.csrf_token) updateCsrfTokens(data.csrf_token);

            } catch (err) {
                console.error(err);
                showMessage(err.message || 'Request failed', 'error');
            } finally {
                button.disabled = false;
            }
        }

        // Update CSRF hidden inputs on the page
        function updateCsrfTokens(token) {
            if (!token) return;
            document.querySelectorAll('.csrf-token-input').forEach(inp => inp.value = token);
        }

        // Global delegated click handler to keep markup unchanged
        document.addEventListener('click', function (e) {
            const approveBtn = e.target.closest('.approve-btn');
            if (approveBtn) {
                const form = approveBtn.closest('form.ajax-action-form');
                if (!form) return;
                if (!confirm('Approve this application and create scholar account?')) return;
                submitAjaxForm(form, approveBtn);
                return;
            }

            const rejectBtn = e.target.closest('.reject-btn');
            if (rejectBtn) {
                const form = rejectBtn.closest('form.ajax-action-form');
                if (!form) return;
                if (!confirm('Reject and delete this application?')) return;
                submitAjaxForm(form, rejectBtn);
                return;
            }
        });
    </script>
</body>
</html>
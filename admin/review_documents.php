<?php
session_start();
require '../config.php';

// Ensure $pdo exists
/*if (!isset($pdo) || !$pdo) {
  //  try {
   //   $pdo = new PDO('mysql:host=localhost;dbname=scholarship_system', 'root', '');
   //   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   / } catch (PDOException $e) {
   /   error_log('Database connection failed in admin/review_documents.php: ' . $e->getMessage());
   /   die('Database connection failed. Please contact the administrator.');
    }
}*/

// Admin check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Filters
$search_query = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? 'pending'); // Default to pending (lowercase to match DB enum)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

// Total scholars for pagination (count distinct scholars)
$total_sql = "SELECT COUNT(DISTINCT scholars.id) FROM documents 
        JOIN scholars ON documents.scholar_id = scholars.id
        WHERE documents.status = ?
        AND (CONCAT_WS(' ', scholars.first_name, scholars.middle_name, scholars.last_name) LIKE ? 
          OR documents.document_type LIKE ?)";
$total_stmt = $pdo->prepare($total_sql);
$total_stmt->execute([$status_filter, "%$search_query%", "%$search_query%"]);
$total_scholars = (int)$total_stmt->fetchColumn();
$total_pages = max(1, ceil($total_scholars / $limit));
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $limit;

// Fetch document overview counts (from documents table, not scholar_credentials)
$sql_counts = "SELECT 
      COUNT(*) AS total,
      COUNT(CASE WHEN status = 'Pending' THEN 1 END) AS pending_count,
      COUNT(CASE WHEN status = 'Approved' THEN 1 END) AS approved_count,
      COUNT(CASE WHEN status = 'Rejected' THEN 1 END) AS rejected_count
    FROM documents";
$counts = $pdo->query($sql_counts)->fetch(PDO::FETCH_ASSOC);

// Fetch document type distribution (all statuses, use correct column)
$sql_type_dist = "SELECT document_type, COUNT(*) as count FROM documents GROUP BY document_type ORDER BY count DESC";
$type_dist = $pdo->query($sql_type_dist)->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent activity (last 7 days)
$sql_recent = "SELECT status, COUNT(*) as count, DATE(uploaded_at) as day FROM documents WHERE uploaded_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY day, status ORDER BY day DESC";
$recent_activity = $pdo->query($sql_recent)->fetchAll(PDO::FETCH_ASSOC);

// Fetch scholars and their documents
$sql_scholars = "SELECT DISTINCT scholars.id AS scholar_id, 
        CONCAT_WS(' ', scholars.first_name, scholars.middle_name, scholars.last_name) AS full_name
      FROM documents 
      JOIN scholars ON documents.scholar_id = scholars.id
      WHERE documents.status = ?
      AND (scholars.first_name LIKE ? OR scholars.middle_name LIKE ? OR scholars.last_name LIKE ? OR documents.document_type LIKE ?)
      ORDER BY scholars.last_name, scholars.first_name
      LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql_scholars);
$stmt->bindValue(1, $status_filter, PDO::PARAM_STR);
$stmt->bindValue(2, "%$search_query%", PDO::PARAM_STR);
$stmt->bindValue(3, "%$search_query%", PDO::PARAM_STR);
$stmt->bindValue(4, "%$search_query%", PDO::PARAM_STR);
$stmt->bindValue(5, "%$search_query%", PDO::PARAM_STR);
$stmt->bindValue(6, $limit, PDO::PARAM_INT);
$stmt->bindValue(7, $offset, PDO::PARAM_INT);
$stmt->execute();
$scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch documents for each scholar
$documents_by_scholar = [];
foreach ($scholars as $scholar) {
    $sql_docs = "SELECT documents.id, documents.document_type, documents.file_path, documents.status, documents.physical_copy_confirmed
            
           FROM documents
           WHERE documents.scholar_id = ? AND documents.status = ?
           ORDER BY documents.id DESC";
    $stmt = $pdo->prepare($sql_docs);
    $stmt->execute([$scholar['scholar_id'], $status_filter]);
    $documents_by_scholar[$scholar['scholar_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Main admin view check
$stmt = $pdo->prepare("SELECT main_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$is_main_admin = ($user && $user['main_admin'] == 1) && isset($_GET['main_view']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review Documents</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .card-doc { transition: transform 0.3s, box-shadow 0.3s; }
    .card-doc:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .status-active { border: 2px solid #2563eb; }
    .sub-row { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-in-out; }
    .sub-row.active { max-height: 500px; } /* Adjust based on expected content height */
    .toggle-icon { display: inline-block; width: 1em; transition: transform 0.3s; }
    .toggle-icon.active { transform: rotate(90deg); }
    .toggle-icon::before { content: '\25B6'; }
    .fade-out { opacity: 0; transition: opacity 0.3s ease-out; }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="border-b mb-4"></div>


  <main class="container mx-auto px-4 py-3">

    <!-- Enhanced Analytics Section -->
    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Document Type Distribution -->
      <div class="bg-white shadow rounded-lg p-4">
        <h4 class="font-semibold mb-2 text-blue-700 flex items-center gap-2"><i class="bi bi-bar-chart"></i> Document Type Distribution</h4>
        <?php if (!empty($type_dist)): ?>
          <ul class="text-sm">
            <?php foreach ($type_dist as $row): ?>
              <li class="flex justify-between border-b last:border-b-0 py-1">
                <span><?= htmlspecialchars($row['document_type']) ?></span>
                <span class="font-bold text-blue-600"><?= $row['count'] ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <span class="text-gray-500">No data</span>
        <?php endif; ?>
      </div>
      <!-- Recent Activity (last 7 days) -->
      <div class="bg-white shadow rounded-lg p-4">
        <h4 class="font-semibold mb-2 text-green-700 flex items-center gap-2"><i class="bi bi-clock-history"></i> Recent Activity (7 days)</h4>
        <?php if (!empty($recent_activity)): ?>
          <ul class="text-sm">
            <?php $last_day = null; foreach ($recent_activity as $row): ?>
              <?php if ($last_day !== $row['day']): ?>
                <li class="mt-2 font-bold text-gray-700"><?= htmlspecialchars($row['day']) ?></li>
                <?php $last_day = $row['day']; ?>
              <?php endif; ?>
              <li class="ml-4 flex justify-between">
                <span><?= ucfirst($row['status']) ?></span>
                <span class="font-bold <?= $row['status']==='approved'?'text-green-600':($row['status']==='rejected'?'text-red-600':'text-yellow-600') ?>"><?= $row['count'] ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <span class="text-gray-500">No recent activity</span>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($is_main_admin): ?>
    <div class="mb-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 p-3 flex items-center gap-2">
      <i class="bi bi-shield-lock"></i>
      <span>You are viewing as <b>Main Admin</b>. This page is <b>read-only</b>.</span>
    </div>
    <?php endif; ?>

    <!-- Overview Cards -->
    <div class="flex flex-wrap gap-3 mb-4" id="overview-cards">
      <a href="review_documents.php?status=pending&search=<?= urlencode($search_query) ?>"
         class="flex-1 bg-white shadow-md p-4 rounded-xl text-center hover:shadow-lg transition <?= $status_filter==='pending'?'status-active':'' ?>">
        <i class="bi bi-hourglass-split text-3xl text-yellow-500"></i>
        <h5 class="mt-2 font-semibold">Pending</h5>
        <span class="inline-block bg-yellow-200 text-yellow-800 text-lg font-bold px-3 py-1 rounded-full" id="pending-count"><?= isset($counts['pending_count']) ? $counts['pending_count'] : (isset($counts['pending']) ? $counts['pending'] : 0) ?></span>
      </a>
      <a href="review_documents.php?status=approved&search=<?= urlencode($search_query) ?>"
         class="flex-1 bg-white shadow-md p-4 rounded-xl text-center hover:shadow-lg transition <?= $status_filter==='approved'?'status-active':'' ?>">
        <i class="bi bi-check-circle text-3xl text-green-500"></i>
        <h5 class="mt-2 font-semibold">Approved</h5>
        <span class="inline-block bg-green-200 text-green-800 text-lg font-bold px-3 py-1 rounded-full" id="approved-count"><?= isset($counts['approved_count']) ? $counts['approved_count'] : (isset($counts['approved']) ? $counts['approved'] : 0) ?></span>
      </a>
      <a href="review_documents.php?status=rejected&search=<?= urlencode($search_query) ?>"
         class="flex-1 bg-white shadow-md p-4 rounded-xl text-center hover:shadow-lg transition <?= $status_filter==='rejected'?'status-active':'' ?>">
        <i class="bi bi-x-circle text-3xl text-red-500"></i>
        <h5 class="mt-2 font-semibold">Rejected</h5>
        <span class="inline-block bg-red-200 text-red-800 text-lg font-bold px-3 py-1 rounded-full" id="rejected-count"><?= isset($counts['rejected_count']) ? $counts['rejected_count'] : (isset($counts['rejected']) ? $counts['rejected'] : 0) ?></span>
      </a>
    </div>

    <!-- SMS toggle -->
    <div class="flex items-center mb-3">
      <input id="sms-toggle" type="checkbox" checked class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
      <label for="sms-toggle" class="ml-2 text-gray-700">SMS Notifications</label>
    </div>

    <!-- Search -->
    <div class="bg-white shadow-md rounded-xl mb-3">
      <div class="bg-blue-600 text-white px-4 py-2 rounded-t-xl font-semibold">Search Documents</div>
      <div class="p-4">
        <form method="GET" action="review_documents.php" class="flex flex-wrap gap-2 mb-3">
          <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
          <div class="flex-grow flex">
            <input type="text" name="search" placeholder="Search by scholar name or document type"
              class="w-full border rounded-l-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
              value="<?= htmlspecialchars($search_query) ?>" autofocus autocomplete="off">
            <?php if ($search_query): ?>
              <a href="review_documents.php?status=<?= urlencode($status_filter) ?>" class="px-3 flex items-center bg-gray-100 border border-l-0 rounded-r-md hover:bg-gray-200">
                <i class="bi bi-x"></i>
              </a>
            <?php endif; ?>
          </div>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-1">
            <i class="bi bi-search"></i> Search
          </button>
        </form>

        <!-- Batch Actions -->
        <?php if (!$is_main_admin && !empty($scholars) && strtolower($status_filter)==='pending'): ?>
        <div class="mb-2 flex items-center gap-2">
          <button id="batch-approve" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm flex items-center gap-1">
            <i class="bi bi-check-circle"></i> Batch Approve
          </button>
          <button id="batch-reject" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm flex items-center gap-1">
            <i class="bi bi-x-circle"></i> Batch Reject
          </button>
          <input id="select-all" type="checkbox" class="ml-3 h-4 w-4 border-gray-300 rounded">
          <label for="select-all" class="ml-1 text-gray-700 text-sm">Select All</label>
        </div>
        <?php endif; ?>

        <!-- Scholar and Document List -->
        <div id="documents-list">
          <?php if (empty($scholars)): ?>
            <div class="text-center text-gray-500">No <?= htmlspecialchars($status_filter) ?> documents found.</div>
          <?php else: ?>
            <?php foreach ($scholars as $scholar): ?>
            <div class="bg-white shadow-sm p-3 rounded-lg mb-3 card-doc" data-scholar-id="<?= $scholar['scholar_id'] ?>">
              <!-- Scholar Row -->
              <div class="scholar-row flex items-center cursor-pointer" onclick="toggleSubRow(<?= $scholar['scholar_id'] ?>)">
                <span class="toggle-icon mr-2"></span>
                <strong class="text-lg"><?= htmlspecialchars($scholar['full_name']) ?></strong>
              </div>
              <!-- Document Sub-Row -->
              <div class="sub-row" id="sub-row-<?= $scholar['scholar_id'] ?>">
                <?php foreach ($documents_by_scholar[$scholar['scholar_id']] as $doc): ?>
                <div class="bg-gray-50 p-3 border-t border-gray-200" data-id="<?= $doc['id'] ?>">
                  <div class="grid grid-cols-12 items-center gap-2">
                    <?php if (!$is_main_admin && strtolower($status_filter)==='pending'): ?>
                    <div class="col-span-1">
                      <input type="checkbox" class="doc-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded"
                             value="<?= $doc['id'] ?>">
                    </div>
                    <?php endif; ?>
                    <div class="col-span-3">
                      <small class="text-gray-600"><?= htmlspecialchars($doc['document_type']) ?></small>
                    </div>
                    <div class="col-span-2">
                      <span class="status-badge px-2 py-1 rounded-full text-xs font-medium
                        <?= $doc['status']==='Pending'?'bg-yellow-200 text-yellow-800':($doc['status']==='Approved'?'bg-green-200 text-green-800':'bg-red-200 text-red-800') ?>">
                        <?= $doc['status'] ?>
                      </span>
                    </div>
                    <div class="col-span-2">
                      <?php if (!empty($doc['file_path']) && file_exists("../" . $doc['file_path'])): ?>
                        <a href="../<?= $doc['file_path'] ?>" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-2 py-1 rounded-md">
                          <i class="bi bi-eye"></i> View
                        </a>
                      <?php else: ?> N/A <?php endif; ?>
                    </div>
                    <div class="col-span-4 text-right">
                      <?php if (!$is_main_admin && strtolower($status_filter)==='pending'): ?>
                        <label class="inline-flex items-center mr-2">
                          <input type="checkbox" class="physical-copy-toggle h-4 w-4 text-blue-600 border-gray-300 rounded"
                            data-id="<?= $doc['id'] ?>" <?= $doc['physical_copy_confirmed'] ? 'checked' : '' ?>>
                          <span class="ml-1 text-sm text-gray-700">Physical</span>
                        </label>
                        <button class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded-md text-sm action-btn"
                                data-id="<?= $doc['id'] ?>" data-action="approve" title="Approve">
                          <i class="bi bi-check-circle"></i> Approve
                        </button>
                        <button class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded-md text-sm action-btn"
                                data-id="<?= $doc['id'] ?>" data-action="reject" title="Reject">
                          <i class="bi bi-x-circle"></i> Reject
                        </button>
                      <?php else: ?>
                        <span class="text-gray-400">Read-only</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <nav class="mt-3">
          <ul class="flex justify-center gap-1">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li>
                <a class="px-3 py-1 rounded-md border <?= $i===$page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>"
                   href="review_documents.php?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_query) ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>

      </div>
    </div>
  </main>
<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const currentStatusFilter = '<?= htmlspecialchars($status_filter) ?>';

// Toggle sub-row visibility with smooth transition
function toggleSubRow(scholarId) {
    const subRow = document.getElementById(`sub-row-${scholarId}`);
    const toggleIcon = document.querySelector(`.card-doc[data-scholar-id='${scholarId}'] .toggle-icon`);
    if (subRow.classList.contains('active')) {
        subRow.classList.remove('active');
        toggleIcon.classList.remove('active');
    } else {
        subRow.classList.add('active');
        toggleIcon.classList.add('active');
    }
}

// On page load, ensure approve buttons and doc-checkboxes reflect physical copy state
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.physical-copy-toggle').forEach(cb => {
    const docRow = cb.closest('[data-id]');
    const approveBtn = docRow ? docRow.querySelector("[data-action='approve']") : null;
    const batchCb = docRow ? docRow.querySelector('.doc-checkbox') : null;
    if (approveBtn) approveBtn.disabled = !cb.checked;
    if (batchCb) batchCb.disabled = !cb.checked;
  });
});

// Select all checkbox
document.getElementById('select-all')?.addEventListener('change', function() {
    document.querySelectorAll('.doc-checkbox:not(:disabled)').forEach(cb => cb.checked = this.checked);
});

// Helper to get SMS toggle state
function smsEnabled() {
    const el = document.getElementById('sms-toggle');
    return el ? !!el.checked : true;
}

// Single Approve/Reject AJAX
document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const docId = this.dataset.id;
        const action = this.dataset.action;
        if (!confirm(`Are you sure you want to ${action} this document?`)) return;
        fetch('review_documents_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                document_ids: [docId],
                action: action,
                sms_enabled: smsEnabled()
            })
        })
        .then(res => res.json())
        .then(updateUI)
        .catch(console.error);
    });
});

// Batch Approve/Reject
document.getElementById('batch-approve')?.addEventListener('click', () => batchAction('approve'));
document.getElementById('batch-reject')?.addEventListener('click', () => batchAction('reject'));

function batchAction(action) {
    let selected = Array.from(document.querySelectorAll('.doc-checkbox:checked')).map(cb => cb.value);
    if (!selected.length) { alert('No documents selected or missing physical copy.'); return; }
    if (!confirm(`Are you sure you want to ${action} selected documents?`)) return;
    fetch('review_documents_ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            document_ids: selected,
            action: action,
            sms_enabled: smsEnabled()
        })
    })
    .then(res => res.json())
    .then(updateUI)
    .catch(console.error);
}

// Toggle physical copy confirmation
document.querySelectorAll('.physical-copy-toggle').forEach(cb => {
    cb.addEventListener('change', function() {
        const self = this;
        fetch('review_documents_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                document_ids: [this.dataset.id],
                action: 'toggle_physical',
                confirmed: this.checked ? 1 : 0
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Failed to update');
                self.checked = !self.checked; // revert
                return;
            }
            // Enable/disable approve + batch checkbox with smooth transition
            const docRow = self.closest('[data-id]');
            const approveBtn = docRow.querySelector("[data-action='approve']");
            const batchCb = docRow.querySelector(".doc-checkbox");
            if (approveBtn) approveBtn.disabled = !self.checked;
            if (batchCb) batchCb.disabled = !self.checked;
        })
        .catch(console.error);
    });
});

// Update UI after AJAX with smooth transitions
function updateUI(data) {
    if (!data.success) { alert(data.message || 'Action failed'); return; }

    function badgeClassesFor(status) {
        const s = String(status).toLowerCase();
        if (s === 'approved') return 'bg-green-200 text-green-800';
        if (s === 'rejected') return 'bg-red-200 text-red-800';
        return 'bg-yellow-200 text-yellow-800';
    }

    data.updated.forEach(u => {
        const docRow = document.querySelector(`[data-id='${u.id}']`);
        if (docRow) {
            const badge = docRow.querySelector('.status-badge');
            if (badge) {
                badge.textContent = u.status;
                const classes = badgeClassesFor(u.status);
                badge.className = `status-badge px-2 py-1 rounded-full text-xs font-medium ${classes}`;
            }
            const physicalToggle = docRow.querySelector('.physical-copy-toggle');
            if (physicalToggle) physicalToggle.disabled = true;
            docRow.querySelectorAll('.action-btn').forEach(b => b.remove());
            docRow.querySelector('.doc-checkbox')?.remove();

            // If status changed and doesn't match filter, fade out and remove
            if (u.status !== currentStatusFilter) {
                docRow.classList.add('fade-out');
                setTimeout(() => docRow.remove(), 300);
            }
        }
    });

    // Remove empty scholar rows with fade
    document.querySelectorAll('.card-doc').forEach(card => {
        const subRow = card.querySelector('.sub-row');
        if (subRow && !subRow.querySelector('[data-id]')) {
            card.classList.add('fade-out');
            setTimeout(() => card.remove(), 300);
        }
    });

    // Update counts
    document.getElementById('pending-count').textContent = data.counts.pending;
    document.getElementById('approved-count').textContent = data.counts.approved;
    document.getElementById('rejected-count').textContent = data.counts.rejected;

    // Reset select all if needed
    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = false;

    // If no documents remain, refresh the page to reflect pagination
    if (!document.querySelector('#documents-list .card-doc')) {
        window.location.reload();
    }
}
</script>
</body>
</html>
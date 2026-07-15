<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$scholar_id = $_GET['scholar_id'] ?? null;
if (!$scholar_id) { echo "Scholar not specified."; exit; }

// --- HANDLE DOCUMENT DELETE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_document_id'])) {
    $delete_id = intval($_POST['delete_document_id']);
    $stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ? AND scholar_id = ?");
    $stmt->execute([$delete_id, $scholar_id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doc) {
        $file_path = "../" . $doc['file_path'];
        if (file_exists($file_path)) unlink($file_path);

        $del_stmt = $conn->prepare("DELETE FROM documents WHERE id = ? AND scholar_id = ?");
        $del_stmt->execute([$delete_id, $scholar_id]);
        $_SESSION['message'] = "Document deleted successfully.";
    } else {
        $_SESSION['error'] = "Document not found or already deleted.";
    }
    header("Location: scholar_documents.php?scholar_id=" . urlencode($scholar_id));
    exit;
}

// Fetch scholar info
$scholar_stmt = $conn->prepare("SELECT first_name, middle_name, last_name, profile_pic FROM scholars WHERE id = ?");
$scholar_stmt->execute([$scholar_id]);
$scholar = $scholar_stmt->fetch(PDO::FETCH_ASSOC);
if (!$scholar) { echo "Scholar not found."; exit; }

// Fetch all documents
$docs_stmt = $conn->prepare("SELECT * FROM documents WHERE scholar_id = ?");
$docs_stmt->execute([$scholar_id]);
$documents = $docs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch requirements and group deadline for permanent requirements
$reqs = $conn->query("SELECT * FROM requirements ORDER BY is_permanent DESC, tag ASC")->fetchAll(PDO::FETCH_ASSOC);
$groupDeadline = null;
foreach ($reqs as $r) {
  if (isset($r['is_permanent']) && $r['is_permanent']) {
    $groupDeadline = $r['deadline'];
    break;
  }
}

// Map uploaded docs (case-insensitive, only approved count as complete)
$uploaded = [];
foreach ($documents as $doc) {
  $key = mb_strtolower($doc['document_type']);
  // Only mark as 'complete' if approved, but still show others
  if (!isset($uploaded[$key]) || $doc['status'] === 'Approved') {
    $uploaded[$key] = $doc;
  }
}


// Fetch LOA (if any)
$loa_stmt = $conn->prepare("SELECT * FROM documents WHERE scholar_id = ? AND document_type = 'LOA' LIMIT 1");
$loa_stmt->execute([$scholar_id]);
$loa = $loa_stmt->fetch(PDO::FETCH_ASSOC);

// Check if LOA is required for this scholar
$special_stmt = $conn->prepare("SELECT required FROM special_cases WHERE scholar_id = ? AND case_type = 'LOA' LIMIT 1");
$special_stmt->execute([$scholar_id]);
$special_case = $special_stmt->fetch(PDO::FETCH_ASSOC);
$loa_required = $special_case && $special_case['required'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scholar Documents</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/css/glightbox.min.css"/>
</head>
<body class="bg-gray-50 text-gray-800">

<?php include __DIR__ . '/includes/navbar.php'; ?>
<div class="h-[56px]"></div>
<div class="border-b mb-4"></div>

<div class="container mx-auto px-4 py-6">
  <a href="dashboard.php" 
     class="inline-flex items-center px-4 py-2 mb-4 text-sm font-medium text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition">
    <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
  </a>

  <h1 class="text-2xl font-semibold text-blue-700 mb-6">
    Documents for <?= htmlspecialchars(trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['middle_name'] ?? '') . ' ' . ($scholar['last_name'] ?? ''))) ?>
  </h1>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="mb-4 p-3 text-green-700 bg-green-100 border border-green-300 rounded-lg flex justify-between items-center">
      <span><?= $_SESSION['message']; unset($_SESSION['message']); ?></span>
      <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="mb-4 p-3 text-red-700 bg-red-100 border border-red-300 rounded-lg flex justify-between items-center">
      <span><?= $_SESSION['error']; unset($_SESSION['error']); ?></span>
      <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  <?php endif; ?>

  <!-- Requirements Table -->
  <div class="overflow-x-auto border rounded-lg shadow">
    <table id="docTable" class="min-w-full text-sm border-collapse">
      <thead class="bg-gray-100 sticky top-0 shadow-sm">
        <tr class="text-left text-gray-600 uppercase text-xs font-semibold tracking-wide">
          <th class="px-4 py-3">Document Type</th>
          <th class="px-4 py-3">Deadline</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">File</th>
          <th class="px-4 py-3 text-center">Physical Copy</th>
          <th class="px-4 py-3 text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reqs as $req):
          $key = mb_strtolower($req['document_type']);
          $doc = $uploaded[$key] ?? null;
          // Use group deadline for permanent requirements (COG, COR, ID)
          $deadline = ($req['is_permanent'] ?? 0) ? $groupDeadline : $req['deadline'];
          // Only show as complete if approved
          $is_complete = $doc && $doc['status'] === 'Approved';
          $status = $doc ? htmlspecialchars($doc['status']) : '<span class="text-red-600 font-bold">Not Submitted</span>';
          $deadline_display = $deadline ?: '-';
          if ($deadline) {
              $today = date('Y-m-d');
              $diff = strtotime($deadline)-strtotime($today);
              if ($diff < 0) $deadline_display .= ' <span class="text-red-600 font-bold">(Overdue!)</span>';
              elseif ($diff <= 3*24*60*60) $deadline_display .= ' <span class="text-yellow-600 font-bold">(Due soon!)</span>';
          }
        ?>
          <tr class="<?= $is_complete ? 'bg-green-50 hover:bg-green-100' : 'bg-red-50 hover:bg-red-100' ?> transition">
            <td class="px-4 py-3"><?= htmlspecialchars($req['document_type']) ?></td>
            <td class="px-4 py-3"><?= $deadline_display ?></td>
            <td class="px-4 py-3"><?= $status ?></td>
            <td class="px-4 py-3 text-center">
              <?php if ($doc && !empty($doc['file_path']) && file_exists("../".$doc['file_path'])):
                $file_path = "../" . htmlspecialchars($doc['file_path']);
                $ext = strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png'])): ?>
                  <a href="<?= $file_path ?>" class="glightbox" title="<?= htmlspecialchars($req['document_type']) ?>">
                    <img src="<?= $file_path ?>" class="w-14 h-14 object-cover rounded border">
                  </a>
                <?php elseif ($ext==='pdf'): ?>
                  <a href="<?= $file_path ?>" target="_blank" title="View PDF">
                    <i class="bi bi-file-earmark-pdf text-2xl text-red-600"></i>
                  </a>
                <?php else: ?>
                  <a href="<?= $file_path ?>" target="_blank" class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 transition">View</a>
                <?php endif;
              else: ?> N/A <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center">
              <?php if ($doc): ?>
                <i class="bi <?= $doc['physical_copy_confirmed'] ? 'bi-check-circle-fill text-green-600' : 'bi-x-circle-fill text-red-600'; ?> text-xl cursor-pointer physical-check"
                   data-id="<?= $doc['id']; ?>" title="Toggle Physical Copy"></i>
              <?php else: ?> - <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center space-x-1">
              <?php if ($doc && !empty($doc['file_path']) && file_exists("../".$doc['file_path'])): ?>
                <a href="<?= $file_path ?>" download class="inline-flex items-center px-2 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600 transition">
                  <i class="bi bi-download"></i>
                </a>
                <a href="<?= $file_path ?>" target="_blank" class="inline-flex items-center px-2 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                  <i class="bi bi-printer"></i>
                </a>
                <form method="POST" class="inline-block" onsubmit="return confirm('Delete this file?');">
                  <input type="hidden" name="delete_document_id" value="<?= $doc['id'] ?>">
                  <button type="submit" class="inline-flex items-center px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              <?php else: ?> - <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Special Submissions Section -->
  <div class="mt-10">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-blue-700">Special Submissions (Optional)</h2>
      <form method="POST" action="toggle_loa.php" class="flex items-center space-x-2">
        <input type="hidden" name="scholar_id" value="<?= $scholar_id ?>">
        <button type="submit" name="toggle_loa" value="1"
          class="px-3 py-1 rounded-lg text-sm font-medium 
            <?= $loa_required ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-green-500 text-white hover:bg-green-600' ?>">
          <?= $loa_required ? 'Disable LOA' : 'Require LOA' ?>
        </button>
      </form>
    </div>

    <?php if ($loa_required): ?>
      <div class="border rounded-lg shadow bg-white">
        <div class="p-4">
          <h3 class="text-lg font-medium mb-3">Leave of Absence (LOA)</h3>

          <?php if ($loa): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p class="font-medium text-gray-800">Status: 
                  <span class="px-2 py-1 text-xs rounded 
                    <?= $loa['status'] === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                    <?= htmlspecialchars($loa['status']) ?>
                  </span>
                </p>
                <p class="text-sm text-gray-600">Submitted on: <?= htmlspecialchars($loa['created_at'] ?? 'N/A') ?></p>
              </div>
              <div class="flex space-x-2">
                <?php 
                  $file_path = "../" . htmlspecialchars($loa['file_path']);
                  $ext = strtolower(pathinfo($loa['file_path'], PATHINFO_EXTENSION));
                ?>
                <?php if (in_array($ext, ['jpg','jpeg','png'])): ?>
                  <a href="<?= $file_path ?>" class="glightbox" title="LOA">
                    <img src="<?= $file_path ?>" class="w-14 h-14 object-cover rounded border">
                  </a>
                <?php elseif ($ext === 'pdf'): ?>
                  <a href="<?= $file_path ?>" target="_blank">
                    <i class="bi bi-file-earmark-pdf text-3xl text-red-600"></i>
                  </a>
                <?php else: ?>
                  <a href="<?= $file_path ?>" target="_blank" class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 transition">View</a>
                <?php endif; ?>

                <a href="<?= $file_path ?>" download class="inline-flex items-center px-2 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600 transition">
                  <i class="bi bi-download"></i>
                </a>
                <form method="POST" onsubmit="return confirm('Delete this LOA?');">
                  <input type="hidden" name="delete_document_id" value="<?= $loa['id'] ?>">
                  <button type="submit" class="inline-flex items-center px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          <?php else: ?>
            <p class="text-gray-600 italic">No LOA filed by this scholar.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <p class="text-gray-400 italic">LOA not required for this scholar.</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/glightbox.min.js"></script>
<script>
const lightbox = GLightbox({ selector: '.glightbox' });

// AJAX Physical Copy Toggle
document.querySelectorAll('.physical-check').forEach(icon=>{
    icon.addEventListener('click', ()=>{
        const id = icon.getAttribute('data-id');
        const xhr = new XMLHttpRequest();
        xhr.open('POST','ajax_toggle_physical.php',true);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.onload = function() {
            if(this.status===200 && this.responseText.trim()==='ok'){
                if(icon.classList.contains('bi-check-circle-fill')){
                    icon.classList.remove('bi-check-circle-fill','text-green-600');
                    icon.classList.add('bi-x-circle-fill','text-red-600');
                } else {
                    icon.classList.remove('bi-x-circle-fill','text-red-600');
                    icon.classList.add('bi-check-circle-fill','text-green-600');
                }
            } else {
                alert('Error updating status.');
            }
        };
        xhr.send('id='+id);
    });
});
</script>
</body>
</html>

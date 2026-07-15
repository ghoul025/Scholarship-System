<?php
session_start();
require '../config.php';

// Use DB handle
$db = isset($pdo) ? $pdo : (isset($conn) ? $conn : null);
if (!$db) {
  die('Database connection not available');
}

// Ensure admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// --- Handle Add Requirement ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_requirement'])) {
  $tag = trim($_POST['tag'] ?? '');
  $document_type = trim($_POST['document_type'] ?? '');
  $deadline = isset($_POST['deadline']) && $_POST['deadline'] !== '' ? $_POST['deadline'] : null;
  $allowed_types = trim($_POST['allowed_types'] ?? '');

  if ($tag === '' || $document_type === '' || $allowed_types === '') {
    $_SESSION['message'] = "Please provide tag, document name and allowed types.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  }

  try {
    $stmt = $db->prepare("INSERT INTO requirements (tag, document_type, deadline, allowed_types, is_permanent) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$tag, $document_type, $deadline, $allowed_types]);
    $_SESSION['message'] = "Requirement '" . htmlspecialchars($document_type) . "' added successfully.";
  } catch (Exception $e) {
    $_SESSION['message'] = "Failed to add requirement.";
    if (is_dir(__DIR__ . '/../logs')) error_log(date('c') . ' manage_requirements add error: ' . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/actions.log');
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// --- Handle Delete Requirement ---
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];

  try {
    $stmtCheck = $db->prepare("SELECT is_permanent FROM requirements WHERE id = ?");
    $stmtCheck->execute([$id]);
    $req = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($req && !$req['is_permanent']) {
      $stmtDelete = $db->prepare("DELETE FROM requirements WHERE id = ?");
      $stmtDelete->execute([$id]);
      $_SESSION['message'] = "Requirement deleted successfully.";
    } else {
      $_SESSION['message'] = "Cannot delete a permanent requirement.";
    }
  } catch (Exception $e) {
    $_SESSION['message'] = "Failed to delete requirement.";
    if (is_dir(__DIR__ . '/../logs')) error_log(date('c') . ' manage_requirements delete error: ' . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/actions.log');
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// --- Handle Update Group Deadline ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_group_deadline'])) {
  $deadline = isset($_POST['deadline']) && $_POST['deadline'] !== '' ? $_POST['deadline'] : null;

  if ($deadline) {
    try {
      $stmt = $db->prepare("UPDATE requirements SET deadline = ?, allowed_types = 'pdf' WHERE is_permanent = 1");
      $stmt->execute([$deadline]);
      $_SESSION['message'] = "Group deadline and file type updated successfully.";
    } catch (Exception $e) {
      $_SESSION['message'] = "Failed to update group deadline or file type.";
      if (is_dir(__DIR__ . '/../logs')) error_log(date('c') . ' manage_requirements group deadline error: ' . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/actions.log');
    }
  } else {
    $_SESSION['message'] = "Please provide a valid date.";
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// --- Fetch all requirements BEFORE HTML ---
$reqs = $db->query("SELECT * FROM requirements ORDER BY is_permanent DESC, tag ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get current group deadline (first permanent requirement)
$groupDeadline = null;
foreach ($reqs as $r) {
  if ($r['is_permanent']) {
    $groupDeadline = $r['deadline'];
    break;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Requirements</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-gray-50 text-gray-800">

<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-6">

  <!-- Flash Messages -->
  <div id="flash-message">
    <?php if (isset($_SESSION['message'])): ?>
      <div class="bg-green-100 text-green-700 px-4 py-2 rounded-md shadow flex justify-between items-center mb-4">
        <span><?= $_SESSION['message']; unset($_SESSION['message']); ?></span>
        <button class="text-green-800 hover:text-green-900" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
      </div>
    <?php endif; ?>
  </div>

  <!-- Permanent Requirements -->
  <div class="bg-yellow-100 border border-yellow-300 rounded-lg shadow">
    <div class="bg-yellow-300 px-4 py-2 font-semibold flex items-center gap-2 rounded-t-lg">
      <i class="bi bi-calendar-event"></i> Permanent Requirements (COG, COR, ID)
    </div>
    <div class="p-4 space-y-2">
      <form method="post" class="flex flex-col sm:flex-row gap-3">
        <div class="flex flex-col flex-1">
          <label class="text-sm font-medium">Group Deadline</label>
          <input type="date" name="deadline" value="<?= htmlspecialchars($groupDeadline ?? '') ?>"
            class="rounded border-gray-300 px-2 py-1 shadow-sm focus:border-yellow-400 focus:ring-yellow-400">
        </div>
        <div>
          <button type="submit" name="update_group_deadline" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-4 py-2 rounded shadow transition">
            <i class="bi bi-save"></i> Update
          </button>
        </div>
      </form>
      <p class="text-sm text-gray-600">Deadline applies to <strong>COG</strong>, <strong>COR</strong>, and <strong>ID</strong>. They cannot be deleted and only accept <strong>PDF</strong> files.</p>
    </div>
  </div>

  <!-- Add Requirement Form -->
  <div class="bg-white border rounded-lg shadow">
    <div class="bg-blue-600 text-white px-4 py-2 font-semibold flex items-center gap-2 rounded-t-lg">
      <i class="bi bi-plus-circle"></i> Add Requirement
    </div>
    <div class="p-4">
      <form method="post" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
          <label class="text-sm font-medium">Tag</label>
          <input type="text" name="tag" placeholder="e.g. Clearance"
            class="w-full rounded border-gray-300 px-2 py-1 shadow-sm focus:border-blue-400 focus:ring-blue-400" required>
        </div>
        <div class="md:col-span-2">
          <label class="text-sm font-medium">Document Name</label>
          <input type="text" name="document_type" placeholder="e.g. Barangay Clearance"
            class="w-full rounded border-gray-300 px-2 py-1 shadow-sm focus:border-blue-400 focus:ring-blue-400" required>
        </div>
        <div>
          <label class="text-sm font-medium">Deadline</label>
          <input type="date" name="deadline"
            class="w-full rounded border-gray-300 px-2 py-1 shadow-sm focus:border-blue-400 focus:ring-blue-400">
        </div>
        <div>
          <label class="text-sm font-medium">Allowed Types</label>
          <input type="text" name="allowed_types" placeholder="pdf, jpg, png"
            class="w-full rounded border-gray-300 px-2 py-1 shadow-sm focus:border-blue-400 focus:ring-blue-400" required>
        </div>
        <div class="flex gap-2 items-end">
          <button type="submit" name="add_requirement" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded shadow flex-1">
            <i class="bi bi-plus"></i> Add
          </button>
          <button type="reset" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded shadow">
            <i class="bi bi-x"></i> Clear
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Requirements Table -->
  <div class="bg-white border rounded-lg shadow overflow-hidden">
    <div class="bg-blue-600 text-white px-4 py-2 font-semibold flex items-center gap-2">
      <i class="bi bi-list-ul"></i> Current Requirements
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left border-collapse">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="px-3 py-2 border">Tag</th>
            <th class="px-3 py-2 border">Document Name</th>
            <th class="px-3 py-2 border">Deadline</th>
            <th class="px-3 py-2 border">Allowed Types</th>
            <th class="px-3 py-2 border">Update</th>
            <th class="px-3 py-2 border">Action</th>
            <th class="px-3 py-2 border">Info</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($reqs)): ?>
            <?php foreach($reqs as $req): ?>
              <tr class="hover:bg-gray-50 transition" data-id="<?= $req['id'] ?>">
                <td class="px-3 py-2 border"><?= htmlspecialchars($req['tag']) ?></td>
                <td class="px-3 py-2 border"><?= htmlspecialchars($req['document_type']) ?></td>
                <td class="px-3 py-2 border deadline-cell"><?= $req['deadline'] ?></td>
                <td class="px-3 py-2 border types-cell"><?= htmlspecialchars($req['allowed_types']) ?></td>
                <td class="px-3 py-2 border">
                  <?php if (!$req['is_permanent']): ?>
                    <form class="flex gap-2 items-center requirement-update-form">
                      <input type="date" name="deadline" value="<?= $req['deadline'] ?>"
                        class="rounded border-gray-300 px-2 py-1 text-sm shadow-sm">
                      <input type="text" name="allowed_types" value="<?= htmlspecialchars($req['allowed_types']) ?>"
                        class="rounded border-gray-300 px-2 py-1 text-sm shadow-sm">
                      <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded shadow">
                        <i class="bi bi-save"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="px-2 py-1 rounded bg-gray-300 text-gray-700 text-xs">Permanent (PDF only)</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2 border">
                  <?php if (!$req['is_permanent']): ?>
                    <a href="?delete=<?= $req['id'] ?>" onclick="return confirm('Delete this requirement?');"
                      class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded shadow text-sm">
                      <i class="bi bi-trash"></i>
                    </a>
                  <?php else: ?>
                    <span class="text-gray-400 text-sm">Locked</span>
                  <?php endif; ?>
                </td>
                <td class="px-3 py-2 border">
                  <button type="button" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded shadow text-sm"
                    onclick="alert('Tag: <?= htmlspecialchars($req['tag']) ?>\nDocument: <?= htmlspecialchars($req['document_type']) ?>\nDeadline: <?= $req['deadline'] ?>\nAllowed Types: <?= htmlspecialchars($req['allowed_types']) ?>');">
                    <i class="bi bi-info-circle"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-gray-500 px-3 py-4">No requirements found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<script>
// AJAX update for requirement
document.querySelectorAll('.requirement-update-form').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const tr = this.closest('tr');
    const id = tr.getAttribute('data-id');
    const deadline = this.querySelector('[name="deadline"]').value;
    const allowed_types = this.querySelector('[name="allowed_types"]').value;

    const formData = new URLSearchParams();
    formData.append('id', id);
    formData.append('deadline', deadline);
    formData.append('allowed_types', allowed_types);

    fetch('ajax_update_requirement.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(() => {
      tr.querySelector('.deadline-cell').textContent = deadline;
      tr.querySelector('.types-cell').textContent = allowed_types;
      const flash = document.getElementById('flash-message');
      flash.innerHTML = `<div class="bg-green-100 text-green-700 px-4 py-2 rounded-md shadow flex justify-between items-center mb-4">Requirement updated successfully.<button class="text-green-800 hover:text-green-900" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button></div>`;
    })
    .catch(() => alert('Error updating requirement.'));
  });
});
</script>

</body>
</html>
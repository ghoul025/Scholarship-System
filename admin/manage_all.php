<?php
session_start();
require '../config.php';
require '../includes/school_years.php';

// Ensure $pdo alias exists for code that expects it
if (!isset($pdo) && isset($conn)) $pdo = $conn;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$is_main_admin = false;
$scholars = [];
// detect main_admin flag
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT main_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_main_admin = ($user && $user['main_admin'] == 1) && isset($_GET['main_view']);
}

// Scholars list (joins current school year enrollment if exists)
$sql = "SELECT s.id,
    s.first_name,
    s.middle_name,
    s.last_name,
    s.course,
    s.year_level,
    s.scholarship_type,
    s.phone AS phone,
    s.sex,
    s.units,
    s.tuition_fee,
    s.batch,
    u.username,
    s.status,
    se.school_year_id,
    se.semester
FROM scholars s
JOIN users u ON s.user_id = u.id
LEFT JOIN scholar_enrollments se ON se.scholar_id = s.id AND se.school_year_id = (SELECT id FROM school_years WHERE is_current = 1 LIMIT 1)
ORDER BY s.last_name ASC, s.first_name ASC";
$stmt = $conn->query($sql);
if ($stmt) {
    $scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// School years and current
$years = listSchoolYears();
$current = getCurrentSchoolYear();

// Enrollments filter defaults
$selected_sy = intval($_GET['school_year_id'] ?? ($current['id'] ?? 0));
$selected_sem = $_GET['semester'] ?? '1st';

$enrolls = [];
$sql2 = "SELECT se.*, s.first_name, s.middle_name, s.last_name, s.phone, s.course, s.year_level, s.id AS scholar_id 
         FROM scholar_enrollments se 
         JOIN scholars s ON se.scholar_id = s.id";
$where = [];
$params = [];
if ($selected_sy) { $where[] = 'se.school_year_id = ?'; $params[] = $selected_sy; }
if ($selected_sem) { $where[] = 'se.semester = ?'; $params[] = $selected_sem; }
if ($where) $sql2 .= ' WHERE ' . implode(' AND ', $where);
$sql2 .= ' ORDER BY s.last_name, s.first_name';
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute($params);
$enrolls = $stmt2->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage - Scholars / School Years / Enrollments</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>
<div style="height:56px"></div>
<main class="container py-3" style="max-width:1200px">
  <?php if ($is_main_admin): ?>
    <div class="alert alert-info">You are viewing as <b>Main Admin</b>. Read-only view.</div>
  <?php endif; ?>

  <ul class="nav nav-tabs mb-3" id="manageTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="tab-scholars" data-bs-toggle="tab" data-bs-target="#pane-scholars" type="button" role="tab">Scholars</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-schoolyears" data-bs-toggle="tab" data-bs-target="#pane-schoolyears" type="button" role="tab">School Years</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-enrollments" data-bs-toggle="tab" data-bs-target="#pane-enrollments" type="button" role="tab">Enrollments</button>
    </li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="pane-scholars" role="tabpanel">
      <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <span>Existing Scholars</span>
          <div class="input-group" style="max-width:420px">
            <input id="scholar-filter" class="form-control form-control-sm" />
            <button id="clear-filter" class="btn btn-sm btn-outline-secondary">Clear</button>
          </div>
        </div>
        <div class="card-body p-0">
          <form id="batch-action-form" method="POST" action="actions/batch_actions.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="sticky-top bg-white py-2 px-2 mb-2 border-bottom d-flex flex-wrap align-items-center gap-2 shadow-sm">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <select name="batch_action" id="batch-action-select" class="form-select form-select-sm" style="width:160px;">
                  <option value="">Batch Action</option>
                  <option value="reset">Reset Password</option>
                  <option value="delete">Delete</option>
                  <option value="change_year">Change Year</option>
                  <option value="change_course">Change Course</option>
                  <option value="change_type">Change Type</option>
                  <option value="enroll">Enroll (Batch)</option>
                </select>
                <select name="school_year_id" id="batch-sy-dropdown" class="form-select form-select-sm d-none" style="width:180px">
                  <option value="">Select School Year</option>
                  <?php foreach ($years as $sy): ?>
                    <option value="<?= $sy['id'] ?>" <?= (isset($current) && $current && $current['id']==$sy['id'])? 'selected' : '' ?>><?= htmlspecialchars($sy['label']) ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="semester" id="batch-semester-dropdown" class="form-select form-select-sm d-none" style="width:120px;">
                  <option value="">Semester</option>
                  <option value="1st">1st</option>
                  <option value="2nd">2nd</option>
                </select>
                <select name="new_year_level" id="batch-year-dropdown" class="form-select form-select-sm d-none" style="width:140px;">
                  <option value="">Select Year</option>
                  <option value="1st Year">1st Year</option>
                  <option value="2nd Year">2nd Year</option>
                  <option value="3rd Year">3rd Year</option>
                  <option value="4th Year">4th Year</option>
                </select>
                <select name="new_course" id="batch-course-dropdown" class="form-select form-select-sm d-none" style="width:140px;">
                  <option value="">Select Course</option>
                  <option value="BSCS">BSCS</option>
                  <option value="BSA">BSA</option>
                </select>
                <select name="new_scholarship_type" id="batch-type-dropdown" class="form-select form-select-sm d-none" style="width:140px;">
                  <option value="">Select Type</option>
                  <option value="TES">TES</option>
                  <option value="TDP">TDP</option>
                  <option value="Others">Others</option>
                </select>
                <button type="submit" id="batch-action-btn" class="btn btn-danger btn-sm d-flex align-items-center gap-1" disabled>Apply</button>
              </div>
              <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-1 ms-2" id="export-selected-btn" disabled data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-download"></i> Export Selected
              </button>
              <span class="text-muted small ms-2" id="selected-count"></span>
            </div>
            <div class="table-responsive">
              <table class="table table-striped mb-0" id="scholars-table">
                <thead>
                  <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Username</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Phone</th>
                    <th>Sex</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="scholars-table-body">
                  <?php foreach ($scholars as $scholar): ?>
                    <tr>
                      <td><input class="row-checkbox form-check-input" type="checkbox" value="<?= $scholar['id'] ?>"></td>
                      <td><?= htmlspecialchars($scholar['username']) ?></td>
                      <td><?= htmlspecialchars($scholar['last_name']) ?></td>
                      <td><?= htmlspecialchars($scholar['first_name']) ?></td>
                      <td><?= htmlspecialchars($scholar['middle_name']) ?></td>
                      <td><?= htmlspecialchars($scholar['phone']) ?></td>
                      <td><?= htmlspecialchars($scholar['sex']) ?></td>
                      <td><?= htmlspecialchars($scholar['course']) ?></td>
                      <td><?= htmlspecialchars($scholar['year_level']) ?></td>
                      <td><?= htmlspecialchars($scholar['scholarship_type']) ?></td>
                      <td><?= htmlspecialchars($scholar['status']) ?></td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editScholarModal<?= $scholar['id'] ?>">Edit</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Scholar Modals -->
  <?php foreach ($scholars as $scholar): ?>
  <div class="modal fade" id="editScholarModal<?= $scholar['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST" action="actions/edit_scholar.php">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
              <input type="hidden" name="scholar_id" value="<?= $scholar['id'] ?>">
              <div class="modal-header"><h5 class="modal-title">Edit Scholar</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <div class="mb-2"><label class="form-label">First Name</label><input class="form-control" name="first_name" value="<?= htmlspecialchars($scholar['first_name']) ?>"></div>
                <div class="mb-2"><label class="form-label">Middle Name</label><input class="form-control" name="middle_name" value="<?= htmlspecialchars($scholar['middle_name']) ?>"></div>
                <div class="mb-2"><label class="form-label">Last Name</label><input class="form-control" name="last_name" value="<?= htmlspecialchars($scholar['last_name']) ?>"></div>
                <div class="mb-2"><label class="form-label">Status</label>
                  <select name="status" class="form-select form-select-sm">
                    <option value="enrolled" <?= $scholar['status']=='enrolled' ? 'selected' : '' ?>>Enrolled</option>
                    <option value="not_enrolled" <?= $scholar['status']=='not_enrolled' ? 'selected' : '' ?>>Not Enrolled</option>
                    <option value="graduated" <?= $scholar['status']=='graduated' ? 'selected' : '' ?>>Graduated</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button></div>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <!-- START: Status / Enrollment Modals -->
    <?php foreach ($scholars as $scholar): ?>
    <div class="modal fade" id="statusModal<?= $scholar['id'] ?>" tabindex="-1" aria-labelledby="statusModalLabel<?= $scholar['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <form method="POST" action="actions/update_status.php">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <input type="hidden" name="scholar_id" value="<?= $scholar['id'] ?>">
          <div class="modal-header"><h5 class="modal-title" id="statusModalLabel<?= $scholar['id'] ?>">Update Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="mb-2">
              <select name="status" class="form-select form-select-sm">
                <option value="enrolled" <?= $scholar['status']=='enrolled'? 'selected':'' ?>>Enrolled</option>
                <option value="not_enrolled" <?= $scholar['status']=='not_enrolled'? 'selected':'' ?>>Not Enrolled</option>
                <option value="graduated" <?= $scholar['status']=='graduated'? 'selected':'' ?>>Graduated</option>
              </select>
            </div>
            <div class="mb-2"><label class="form-label">Enroll to School Year</label>
              <select name="school_year_id" class="form-select form-select-sm">
                <option value="">(optional)</option>
                <?php foreach ($years as $sy): ?>
                  <option value="<?= $sy['id'] ?>"><?= htmlspecialchars($sy['label']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2"><label class="form-label">Semester</label>
              <select name="semester" class="form-select form-select-sm">
                <option value="1st">1st</option>
                <option value="2nd">2nd</option>
              </select>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button></div>
        </form>
      </div>
    </div>
    </div>
    <?php endforeach; ?>
    <!-- END: Status / Enrollment Modals -->
  </div>

    <div class="tab-pane fade" id="pane-schoolyears" role="tabpanel">
      <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">School Years</h5>
          <a href="manage_all.php" class="btn btn-sm btn-outline-secondary">Refresh</a>
        </div>
        <div class="card-body">
          <?php if (isset($_SESSION['sy_message'])): ?><div class="alert alert-success"><?php echo $_SESSION['sy_message']; unset($_SESSION['sy_message']); ?></div><?php endif; ?>
          <?php if (isset($_SESSION['sy_error'])): ?><div class="alert alert-danger"><?php echo $_SESSION['sy_error']; unset($_SESSION['sy_error']); ?></div><?php endif; ?>
          <form method="POST" action="actions/school_years.php" class="row g-2 mb-3">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="create">
            <div class="col-md-5"><input type="text" name="label" class="form-control" placeholder="Label e.g. 2024-2025" required></div>
            <div class="col-md-3"><input type="date" name="start_date" class="form-control"></div>
            <div class="col-md-3"><input type="date" name="end_date" class="form-control"></div>
            <div class="col-md-1 d-flex align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_current" id="is_current"><label class="form-check-label small" for="is_current">Current</label></div></div>
            <div class="col-12"><button class="btn btn-primary btn-sm">Add School Year</button></div>
          </form>
          <div class="table-responsive">
            <table class="table table-sm table-striped">
              <thead><tr><th>Label</th><th>Start</th><th>End</th><th>Current</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($years as $y): ?>
                  <tr>
                    <td><?= htmlspecialchars($y['label']) ?></td>
                    <td><?= htmlspecialchars($y['start_date']) ?></td>
                    <td><?= htmlspecialchars($y['end_date']) ?></td>
                    <td><?= $y['is_current'] ? '<span class="badge bg-success">Current</span>' : '' ?></td>
                    <td>
                      <form method="POST" action="actions/school_years.php" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="set_current">
                        <input type="hidden" name="id" value="<?= $y['id'] ?>">
                        <button class="btn btn-sm btn-outline-success">Set Current</button>
                      </form>
                      <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSY<?= $y['id'] ?>">Edit</button>
                      <form method="POST" action="actions/school_years.php" class="d-inline" onsubmit="return confirm('Delete this school year?');">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $y['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                      </form>
                      <div class="modal fade" id="editSY<?= $y['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <form method="POST" action="actions/school_years.php">
                              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                              <div class="modal-header"><h5 class="modal-title">Edit School Year</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                              <div class="modal-body">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $y['id'] ?>">
                                <div class="mb-2"><input type="text" name="label" value="<?= htmlspecialchars($y['label']) ?>" class="form-control" required></div>
                                <div class="mb-2"><input type="date" name="start_date" value="<?= htmlspecialchars($y['start_date']) ?>" class="form-control"></div>
                                <div class="mb-2"><input type="date" name="end_date" value="<?= htmlspecialchars($y['end_date']) ?>" class="form-control"></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_current" id="is_current_<?= $y['id'] ?>" <?= $y['is_current'] ? 'checked' : '' ?>><label class="form-check-label small" for="is_current_<?= $y['id'] ?>">Current</label></div>
                              </div>
                              <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button></div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="pane-enrollments" role="tabpanel">
      <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Enrollments</h5>
          <div>
            <a href="manage_all.php" class="btn btn-sm btn-outline-secondary me-2">Refresh</a>
            <form method="GET" action="manage_all.php" class="d-inline-flex">
              <select name="school_year_id" class="form-select form-select-sm me-2">
                <?php foreach ($years as $y): ?>
                  <option value="<?= $y['id'] ?>" <?= $selected_sy == $y['id'] ? 'selected' : '' ?>><?= htmlspecialchars($y['label']) ?></option>
                <?php endforeach; ?>
              </select>
              <select name="semester" class="form-select form-select-sm me-2">
                <option value="1st" <?= $selected_sem === '1st' ? 'selected' : '' ?>>1st</option>
                <option value="2nd" <?= $selected_sem === '2nd' ? 'selected' : '' ?>>2nd</option>
              </select>
              <button class="btn btn-primary btn-sm">Filter</button>
            </form>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <?php
                // quick counts (for the selected semester)
                if ($selected_sem === '1st') {
                    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM scholar_enrollments WHERE school_year_id = ? AND enrolled_1st = 1');
                    $countStmt->execute([$selected_sy]);
                } else {
                    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM scholar_enrollments WHERE school_year_id = ? AND enrolled_2nd = 1');
                    $countStmt->execute([$selected_sy]);
                }
                $enrolledCount = $countStmt->fetchColumn();
                $totalStmt = $pdo->prepare('SELECT COUNT(*) FROM scholar_enrollments WHERE school_year_id = ?');
                $totalStmt->execute([$selected_sy]);
                $totalCount = $totalStmt->fetchColumn();
              ?>
              <span class="badge bg-success"><?= $enrolledCount ?> enrolled</span>
              <span class="badge bg-secondary ms-2"><?= $totalCount ?> total</span>
            </div>
            <div>
              <form method="POST" action="actions/export_enrollments.php" class="d-inline">
                <input type="hidden" name="school_year_id" value="<?php echo $selected_sy ?>">
                <input type="hidden" name="semester" value="<?php echo htmlspecialchars($selected_sem) ?>">
                <button class="btn btn-success btn-sm"><i class="bi bi-download"></i> Export CSV</button>
              </form>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-sm" id="enrollments-table">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Last Name</th>
                  <th>First Name</th>
                  <th>Middle Name</th>
                  <th>Phone</th>
                  <th>Sex</th>
                  <th>Course</th>
                  <th>Year Level</th>
                  <th>Scholarship Type</th>
                  <th>Semesters</th>
                  <th>Notes</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($enrolls as $e): ?>
                  <tr data-id="<?= $e['id'] ?>" data-scholar="<?= $e['scholar_id'] ?>" data-school-year="<?= intval($e['school_year_id']) ?>">
                    <?php
                      $uStmt = $pdo->prepare('SELECT u.username, s.scholarship_type FROM scholars s JOIN users u ON s.user_id = u.id WHERE s.id = ? LIMIT 1');
                      $uStmt->execute([$e['scholar_id']]);
                      $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <td><?= htmlspecialchars($uRow['username'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['last_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['first_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['middle_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['sex'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['course'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['year_level'] ?? '') ?></td>
                    <td><?= htmlspecialchars($uRow['scholarship_type'] ?? '') ?></td>
                    <td>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-toggle-enrolled-sem btn-1st <?= $e['enrolled_1st'] ? 'btn-success' : 'btn-outline-danger' ?>"><?= $e['enrolled_1st'] ? '1st:Yes' : '1st:No' ?></button>
                        <button class="btn btn-sm btn-toggle-enrolled-sem btn-2nd <?= $e['enrolled_2nd'] ? 'btn-success' : 'btn-outline-danger' ?>"><?= $e['enrolled_2nd'] ? '2nd:Yes' : '2nd:No' ?></button>
                      </div>
                    </td>
                    <td contenteditable="true" class="editable-notes"><?= htmlspecialchars($e['notes']) ?></td>
                    <td><button class="btn btn-sm btn-primary btn-edit-scholar" data-scholar="<?= $e['scholar_id'] ?>">Edit</button></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</main>
<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Export Modal (re-used) -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Scholars tab behaviour
  const filterInput = document.getElementById('scholar-filter');
  const clearBtn = document.getElementById('clear-filter');
  const tableBody = document.getElementById('scholars-table-body');
  const selectAll = document.getElementById('select-all');
  const batchForm = document.getElementById('batch-action-form');
  const batchActionSelect = document.getElementById('batch-action-select');
  const yearDropdown = document.getElementById('batch-year-dropdown');
  const courseDropdown = document.getElementById('batch-course-dropdown');
  const typeDropdown = document.getElementById('batch-type-dropdown');
  const batchSy = document.getElementById('batch-sy-dropdown');
  const batchSem = document.getElementById('batch-semester-dropdown');
  const batchBtn = document.getElementById('batch-action-btn');
  const exportBtn = document.getElementById('export-selected-btn');
  const selectedCount = document.getElementById('selected-count');

  function getRows() { return tableBody ? Array.from(tableBody.querySelectorAll('tr')) : []; }
  function getCheckboxes() { return batchForm ? Array.from(batchForm.querySelectorAll('.row-checkbox')) : []; }
  function getSelectedIds() { return getCheckboxes().filter(cb => cb.checked).map(cb => cb.value); }
  function updateSelectedUI() {
    const ids = getSelectedIds();
    const count = ids.length;
    if (selectedCount) selectedCount.textContent = count > 0 ? `${count} selected` : '';
    if (exportBtn) exportBtn.disabled = count === 0;
    if (batchBtn) batchBtn.disabled = count === 0;
  }

  if (selectAll) {
    selectAll.addEventListener('change', function() { getCheckboxes().forEach(cb => cb.checked = selectAll.checked); updateSelectedUI(); });
  }
  if (batchForm) {
    batchForm.addEventListener('change', updateSelectedUI);
    batchForm.addEventListener('submit', function(e) {
      if (!getSelectedIds().length) { e.preventDefault(); alert('Select at least one scholar'); }
    });
  }
  if (filterInput) { filterInput.placeholder = 'Search username, name, course, year, type, status...'; filterInput.addEventListener('input', function() { const val = filterInput.value.trim().toLowerCase(); getRows().forEach(row => { const text = row.textContent.toLowerCase(); row.style.display = (!val || text.includes(val)) ? '' : 'none'; }); }); }
  if (clearBtn) clearBtn.addEventListener('click', function() { if (filterInput) { filterInput.value=''; filterInput.dispatchEvent(new Event('input')); } });

  // Batch dropdown toggles
  function hideAllDropdowns() { [yearDropdown, courseDropdown, typeDropdown, batchSy, batchSem].forEach(el => { if (!el) return; el.classList.add('d-none'); el.required = false; }); }
  if (batchActionSelect) {
    batchActionSelect.addEventListener('change', function() {
      hideAllDropdowns();
      const v = batchActionSelect.value;
      if (v === 'change_year' && yearDropdown) { yearDropdown.classList.remove('d-none'); yearDropdown.required = true; }
      if (v === 'change_course' && courseDropdown) { courseDropdown.classList.remove('d-none'); courseDropdown.required = true; }
      if (v === 'change_type' && typeDropdown) { typeDropdown.classList.remove('d-none'); typeDropdown.required = true; }
      if (v === 'enroll' && batchSy && batchSem) { batchSy.classList.remove('d-none'); batchSy.required = true; batchSem.classList.remove('d-none'); batchSem.required = true; }
    });
  }

  // Enrollments tab behaviour (toggle enrolled + notes)
  document.querySelectorAll('.btn-toggle-enrolled-sem').forEach(btn => {
    btn.addEventListener('click', function() {
      const tr = btn.closest('tr');
      const scholarId = tr.getAttribute('data-scholar');
      const schoolYear = tr.getAttribute('data-school-year');
      const semester = btn.classList.contains('btn-1st') ? '1st' : '2nd';
      btn.disabled = true;
      const body = 'action=toggle&scholar_id=' + encodeURIComponent(scholarId) + '&semester=' + encodeURIComponent(semester) + '&school_year_id=' + encodeURIComponent(schoolYear);
      fetch('actions/update_enrollment.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: body }).then(r=>r.json()).then(data=>{
        if (data.ok) { btn.textContent = semester + ':' + (data.enrolled ? 'Yes' : 'No'); btn.classList.toggle('btn-success', data.enrolled); btn.classList.toggle('btn-outline-danger', !data.enrolled); } else alert('Failed');
      }).catch(()=>alert('Network error')).finally(()=>btn.disabled=false);
    });
  });
  document.querySelectorAll('.editable-notes').forEach(td => {
    let timeout;
    td.addEventListener('input', function() { clearTimeout(timeout); const tr = td.closest('tr'); const id = tr.getAttribute('data-id'); timeout = setTimeout(function() { fetch('actions/update_enrollment.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: 'action=notes&id=' + encodeURIComponent(id) + '&notes=' + encodeURIComponent(td.textContent.trim()) }).then(r=>r.json()).then(data=>{ if (!data.ok) alert('Failed to save notes'); }).catch(()=>alert('Network error')); }, 700); });
  });
  document.querySelectorAll('.btn-edit-scholar').forEach(btn => { btn.addEventListener('click', function() { const scholarId = btn.getAttribute('data-scholar'); window.location.hash = 'edit-' + scholarId; var el = document.getElementById('editScholarModal' + scholarId); if (el) { var bs = new bootstrap.Modal(el); bs.show(); } }); });

  // Open modal by hash on load
  const hash = (window.location.hash || '').replace('#','');
  if (hash && hash.startsWith('edit-')) { const id = hash.split('-')[1]; const modalEl = document.getElementById('editScholarModal' + id); if (modalEl) { var m = new bootstrap.Modal(modalEl); m.show(); } }
});
</script>
</body>
</html>

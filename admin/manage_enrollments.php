<?php
session_start();
require '../config.php';
require '../includes/school_years.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$years = listSchoolYears();
$selected_sy = intval($_GET['school_year_id'] ?? (getCurrentSchoolYear()['id'] ?? 0));
$selected_sem = $_GET['semester'] ?? '1st';

$sql = "SELECT se.id, se.scholar_id, se.school_year_id, se.enrolled_1st, se.enrolled_2nd, s.first_name, s.middle_name, s.last_name, s.phone, s.course, s.year_level FROM scholar_enrollments se JOIN scholars s ON se.scholar_id = s.id";
$where = [];
$params = [];
if ($selected_sy) { $where[] = 'se.school_year_id = ?'; $params[] = $selected_sy; }
// We no longer filter rows by semester; listing shows both semester flags per school year
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY s.last_name, s.first_name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Enrollments</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../css/style.css">
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>
 <div style="height:56px;"></div>
    <div class="border-bottom" style="margin-bottom:24px;"></div>
<main class="container py-3">
  <div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Enrollments</h5>
      <div>
        <a href="manage_scholars.php" class="btn btn-sm btn-outline-secondary me-2">Back</a>
        <form method="GET" action="manage_enrollments.php" class="d-inline-flex">
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
      <?php if (isset($_SESSION['en_message'])): ?><div class="alert alert-success"><?php echo $_SESSION['en_message']; unset($_SESSION['en_message']); ?></div><?php endif; ?>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
          <strong>Summary:</strong>
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
                  // fetch username and scholarship_type if needed
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
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.btn-toggle-enrolled-sem').forEach(btn => {
    btn.addEventListener('click', function() {
      const tr = btn.closest('tr');
      const scholarId = tr.getAttribute('data-scholar');
      const schoolYear = tr.getAttribute('data-school-year');
      const semester = btn.classList.contains('btn-1st') ? '1st' : '2nd';
      btn.disabled = true;
      const body = 'action=toggle&scholar_id=' + encodeURIComponent(scholarId) + '&semester=' + encodeURIComponent(semester) + '&school_year_id=' + encodeURIComponent(schoolYear);
      fetch('actions/update_enrollment.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: body
      }).then(r=>r.json()).then(data=>{
        if (data.ok) {
          btn.textContent = semester + ':' + (data.enrolled ? 'Yes' : 'No');
          btn.classList.toggle('btn-success', data.enrolled);
          btn.classList.toggle('btn-outline-danger', !data.enrolled);
        } else alert('Failed');
      }).catch(()=>alert('Network error')).finally(()=>btn.disabled=false);
    });
  });
  // notes editing
  document.querySelectorAll('.editable-notes').forEach(td => {
    let timeout;
    td.addEventListener('input', function() {
      clearTimeout(timeout);
      const tr = td.closest('tr');
      const id = tr.getAttribute('data-id');
      timeout = setTimeout(function() {
        fetch('actions/update_enrollment.php', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: 'action=notes&id=' + encodeURIComponent(id) + '&notes=' + encodeURIComponent(td.textContent.trim())
        }).then(r=>r.json()).then(data=>{
          if (!data.ok) alert('Failed to save notes');
        }).catch(()=>alert('Network error'));
      }, 700);
    });
  });
  // edit button -> open manage_scholars modal by anchor (simple solution)
  document.querySelectorAll('.btn-edit-scholar').forEach(btn => {
    btn.addEventListener('click', function() {
      const scholarId = btn.getAttribute('data-scholar');
      // redirect to manage_scholars with anchor to trigger modal
      window.location.href = 'manage_scholars.php#edit-' + scholarId;
    });
  });
});
</script>
</body>
</html>

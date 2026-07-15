<?php
require_once __DIR__ . '/../includes/school_years.php'; // adjust path if needed
$schoolYears = listSchoolYears();
$currentYear = getCurrentSchoolYear();
// School Years panel include for manage_scholars.php
// Assumes the parent page has already included config.php and includes/school_years.php
// Fallback to listSchoolYears() if $years isn't provided by the parent.
if (!isset($years) && function_exists('listSchoolYears')) {
    $years = listSchoolYears();
}
if (!isset($current) && function_exists('getCurrentSchoolYear')) {
    $current = getCurrentSchoolYear();
}
?>
<div class="tab-pane fade" id="pane-schoolyears" role="tabpanel">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">School Years</h5>
            <a href="manage_scholars.php" class="btn btn-sm btn-outline-secondary">Refresh</a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['sy_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['sy_message']; unset($_SESSION['sy_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['sy_error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['sy_error']; unset($_SESSION['sy_error']); ?></div>
            <?php endif; ?>

            <!-- Add School Year Form -->
            <form method="POST" action="actions/school_years.php" class="row g-2 mb-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="create">
                <div class="col-md-5">
                    <input type="text" name="label" class="form-control" placeholder="Label e.g. 2024-2025" required>
                </div>
                <div class="col-md-3"><input type="date" name="start_date" class="form-control"></div>
                <div class="col-md-3"><input type="date" name="end_date" class="form-control"></div>
                <div class="col-md-1 d-flex align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_current" id="is_current">
                        <label class="form-check-label small" for="is_current">Current</label>
                    </div>
                </div>
                <div class="col-12"><button class="btn btn-primary btn-sm">Add School Year</button></div>
            </form>

            <!-- School Years Table -->
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Current</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($years as $y): ?>
                            <tr>
                                <td><?= htmlspecialchars($y['label']) ?></td>
                                <td><?= htmlspecialchars($y['start_date']) ?></td>
                                <td><?= htmlspecialchars($y['end_date']) ?></td>
                                <td><?= $y['is_current'] ? '<span class="badge bg-success">Current</span>' : '' ?></td>
                                <td>
                                    <!-- Set Current -->
                                    <form method="POST" action="actions/school_years.php" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="set_current">
                                        <input type="hidden" name="id" value="<?= $y['id'] ?>">
                                        <button class="btn btn-sm btn-outline-success">Set Current</button>
                                    </form>
                                    <!-- Edit Modal Trigger -->
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSY<?= $y['id'] ?>">Edit</button>
                                    <!-- Delete -->
                                    <form method="POST" action="actions/school_years.php" class="d-inline" onsubmit="return confirm('Delete this school year?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $y['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>

                                    <!-- Edit School Year Modal -->
                                    <div class="modal fade" id="editSY<?= $y['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="actions/school_years.php">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit School Year</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="id" value="<?= $y['id'] ?>">
                                                        <div class="mb-2">
                                                            <input type="text" name="label" value="<?= htmlspecialchars($y['label']) ?>" class="form-control" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <input type="date" name="start_date" value="<?= htmlspecialchars($y['start_date']) ?>" class="form-control">
                                                        </div>
                                                        <div class="mb-2">
                                                            <input type="date" name="end_date" value="<?= htmlspecialchars($y['end_date']) ?>" class="form-control">
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_current" id="is_current_<?= $y['id'] ?>" <?= $y['is_current'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label small" for="is_current_<?= $y['id'] ?>">Current</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-primary btn-sm">Save</button>
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    </div>
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

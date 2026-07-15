<?php
session_start();
require '../../config.php';
require '../../includes/batch_helper.php';
require '../../includes/school_years.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Validate POST and CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['scholar_id'], $_POST['csrf_token'], $_POST['school_year_id'], $_POST['semester'], $_POST['status']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    $_SESSION['batch_error'] = 'Invalid request or CSRF token.';
    header('Location: ../manage_scholars.php');
    exit;
}

$scholar_id = intval($_POST['scholar_id']);
$status = $_POST['status'];
$school_year_id = intval($_POST['school_year_id']);
$semester = $_POST['semester'];

if (!$scholar_id || !$school_year_id || !in_array($semester, ['1st','2nd']) || !in_array($status, ['enrolled','not_enrolled','graduated'])) {
    $_SESSION['batch_error'] = 'Invalid input data.';
    header('Location: ../manage_scholars.php');
    exit;
}

try {
    $conn->beginTransaction();

    // Update scholar status in the main scholars table
    $ok = setScholarStatus($scholar_id, $status);

    // Determine the enrollment column based on semester
    $col = $semester === '1st' ? 'enrolled_1st' : 'enrolled_2nd';

    // Check if enrollment row exists for this scholar + school year
    $stmt = $conn->prepare("SELECT id, enrolled_1st, enrolled_2nd FROM scholar_enrollments WHERE scholar_id = ? AND school_year_id = ?");
    $stmt->execute([$scholar_id, $school_year_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Update the specific semester column based on status
        if ($status === 'enrolled') {
            $stmt = $conn->prepare("UPDATE scholar_enrollments SET $col = 1, updated_at = NOW() WHERE id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE scholar_enrollments SET $col = 0, updated_at = NOW() WHERE id = ?");
        }
        $stmt->execute([$row['id']]);
    } else if ($status === 'enrolled') {
        // Insert new row if none exists and status is 'enrolled'
        $en1 = $semester === '1st' ? 1 : 0;
        $en2 = $semester === '2nd' ? 1 : 0;
        $stmt = $conn->prepare("INSERT INTO scholar_enrollments (scholar_id, school_year_id, enrolled_1st, enrolled_2nd, notes, updated_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$scholar_id, $school_year_id, $en1, $en2, 'Batch enrollment']);
    }

    $conn->commit();
    $_SESSION['batch_message'] = 'Scholar status updated successfully.';
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['batch_error'] = 'Error updating status: ' . $e->getMessage();
    error_log("Error in update_status.php: scholar_id=$scholar_id, status=$status, school_year_id=$school_year_id, semester=$semester, error=" . $e->getMessage());
}

header('Location: ../manage_scholars.php');
exit;
?>

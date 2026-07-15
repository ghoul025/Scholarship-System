<?php
session_start();
require '../config.php';

header('Content-Type: application/json');

// Use available DB handle ($pdo preferred, fallback to $conn)
$db = isset($pdo) ? $pdo : (isset($conn) ? $conn : null);
if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection not available']);
    exit;
}

// Only admin can update
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get POST data safely
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$deadline = isset($_POST['deadline']) && $_POST['deadline'] !== '' ? $_POST['deadline'] : null;
$allowed_types = trim($_POST['allowed_types'] ?? '');

// Basic validation
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid requirement id']);
    exit;
}

try {
    // Check if requirement exists and is not permanent
    $stmtCheck = $db->prepare("SELECT is_permanent FROM requirements WHERE id = ?");
    $stmtCheck->execute([$id]);
    $req = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        echo json_encode(['status' => 'error', 'message' => 'Requirement not found']);
        exit;
    }

    if ($req['is_permanent']) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot update a permanent requirement']);
        exit;
    }

    // Perform update
    $stmt = $db->prepare("UPDATE requirements SET deadline = ?, allowed_types = ? WHERE id = ?");
    $ok = $stmt->execute([$deadline, $allowed_types, $id]);

    if ($ok) {
        echo json_encode(['status' => 'success', 'message' => 'Requirement updated successfully', 'deadline' => $deadline, 'allowed_types' => $allowed_types]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
    }
} catch (Exception $e) {
    // Log error if logs directory exists
    if (is_dir(__DIR__ . '/../logs')) {
        error_log(date('c') . ' ajax_update_requirement error: ' . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/actions.log');
    }
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
?>

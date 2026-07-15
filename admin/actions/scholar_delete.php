<?php
session_start();
require '../../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scholar_id'])) {
    $id = intval($_POST['scholar_id']);
    // Optional: CSRF token check (add to forms for best security)
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     $_SESSION['batch_error'] = 'Invalid CSRF token.';
    //     header('Location: ../manage_scholars.php');
    //     exit;
    // }
    $stmt = $conn->prepare('SELECT user_id FROM scholars WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $user_id = $row['user_id'];
        // Delete exported_credentials
        $conn->prepare('DELETE FROM exported_credentials WHERE scholar_id = ?')->execute([$id]);
        // Delete documents (future: delete files from disk if needed)
        $conn->prepare('DELETE FROM documents WHERE scholar_id = ?')->execute([$id]);
        // Delete scholar
        $conn->prepare('DELETE FROM scholars WHERE id = ?')->execute([$id]);
        // Delete user
        $conn->prepare('DELETE FROM users WHERE id = ?')->execute([$user_id]);
        $_SESSION['batch_message'] = 'Scholar deleted successfully.';
    } else {
        $_SESSION['batch_error'] = 'Scholar not found.';
    }
    header('Location: ../manage_scholars.php');
    exit;
}
$_SESSION['batch_error'] = 'Invalid delete request.';
header('Location: ../manage_scholars.php');
exit;

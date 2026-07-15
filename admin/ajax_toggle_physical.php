<?php
session_start();
require '../config.php';

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') exit;

$id = intval($_POST['id'] ?? 0);

if ($id) {
    // Toggle physical copy confirmation (0 -> 1, 1 -> 0)
    $stmt = $pdo->prepare("UPDATE documents 
                           SET physical_copy_confirmed = CASE 
                               WHEN physical_copy_confirmed = 1 THEN 0 
                               ELSE 1 
                           END 
                           WHERE id = ?");
    $stmt->execute([$id]);

    echo 'ok';
}
?>

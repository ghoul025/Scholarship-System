<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$scholar_id = $_POST['scholar_id'] ?? null;
if (!$scholar_id) { die("Invalid scholar."); }

$stmt = $conn->prepare("SELECT required FROM special_cases WHERE scholar_id = ? AND case_type = 'LOA' LIMIT 1");
$stmt->execute([$scholar_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    // Toggle the value
    $new_status = $row['required'] ? 0 : 1;
    $update = $conn->prepare("UPDATE special_cases SET required = ? WHERE scholar_id = ? AND case_type = 'LOA'");
    $update->execute([$new_status, $scholar_id]);
} else {
    // Insert as required = 1
    $insert = $conn->prepare("INSERT INTO special_cases (scholar_id, case_type, required) VALUES (?, 'LOA', 1)");
    $insert->execute([$scholar_id]);
}

header("Location: scholar_documents.php?scholar_id=" . urlencode($scholar_id));
exit;

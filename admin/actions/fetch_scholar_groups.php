<?php
session_start();
require '../../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
header('Content-Type: application/json');
$groups = [
    'course' => [],
    'year_level' => [],
    'scholarship_type' => []
];
// Group by course
$stmt = $conn->query('SELECT course, COUNT(*) as count FROM scholars GROUP BY course');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $groups['course'][] = $row;
}
// Group by year_level
$stmt = $conn->query('SELECT year_level, COUNT(*) as count FROM scholars GROUP BY year_level');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $groups['year_level'][] = $row;
}
// Group by scholarship_type
$stmt = $conn->query('SELECT scholarship_type, COUNT(*) as count FROM scholars GROUP BY scholarship_type');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $groups['scholarship_type'][] = $row;
}
echo json_encode($groups);

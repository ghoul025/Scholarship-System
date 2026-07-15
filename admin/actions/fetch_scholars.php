<?php
session_start();
require '../../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
header('Content-Type: application/json');
$where = [];
$params = [];
if (!empty($_GET['course'])) {
    $where[] = 's.course = ?';
    $params[] = $_GET['course'];
}
if (!empty($_GET['year_level'])) {
    $where[] = 's.year_level = ?';
    $params[] = $_GET['year_level'];
}
if (!empty($_GET['scholarship_type'])) {
    $where[] = 's.scholarship_type = ?';
    $params[] = $_GET['scholarship_type'];
}
if (!empty($_GET['search'])) {
    $where[] = '(CONCAT_WS(" ", s.first_name, s.middle_name, s.last_name) LIKE ? OR u.username LIKE ? OR s.phone LIKE ?)';
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}
$sql = 'SELECT s.id, u.username, CONCAT_WS(" ", s.first_name, s.middle_name, s.last_name) AS full_name, s.first_name, s.middle_name, s.last_name, s.phone, s.sex, s.units, s.tuition_fee, s.course, s.year_level, s.scholarship_type, s.status FROM scholars s JOIN users u ON s.user_id = u.id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY s.last_name ASC, s.first_name ASC';
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['scholars' => $scholars]);

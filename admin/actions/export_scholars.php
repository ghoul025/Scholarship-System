<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/csrf.php';

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$token = $_POST['csrf_token'] ?? '';
if (!csrf_check($token)) die('CSRF token mismatch');

$format = $_POST['format'] ?? 'csv';
$filters = $_POST['filters'] ?? [];

// Build simple filterable query - expand as needed
$sql = 'SELECT s.id, s.first_name, s.middle_name, s.last_name, s.phone_number, s.batch, s.scholarship_type, s.course, s.year_level FROM scholars s WHERE 1=1';
$params = [];
if (!empty($filters['batch'])) { $sql .= ' AND s.batch = :batch'; $params[':batch'] = $filters['batch']; }
if (!empty($filters['scholarship_type'])) { $sql .= ' AND s.scholarship_type = :stype'; $params[':stype'] = $filters['scholarship_type']; }

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="scholars_export_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array_keys($rows[0] ?? ['id'=>'id','first_name'=>'first_name']));
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out);
    exit;
}

// TODO: add Excel/PDF exports via libraries if needed
$_SESSION['error'] = 'Format not supported yet.';
header('Location: /admin/manage_scholars.php');
exit;

?>

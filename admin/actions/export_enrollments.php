<?php
session_start();
require '../../config.php';
require '../../includes/school_years.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$school_year_id = intval($_POST['school_year_id'] ?? 0);
$semester = $_POST['semester'] ?? '';

$sql = "SELECT se.*, s.first_name, s.middle_name, s.last_name, s.phone, s.course, s.year_level, sy.label as school_year_label FROM scholar_enrollments se JOIN scholars s ON se.scholar_id = s.id LEFT JOIN school_years sy ON se.school_year_id = sy.id";
$where = [];
$params = [];
if ($school_year_id) { $where[] = 'se.school_year_id = ?'; $params[] = $school_year_id; }
if ($semester) {
    if ($semester === '1st') { $where[] = 'se.enrolled_1st = 1'; }
    else if ($semester === '2nd') { $where[] = 'se.enrolled_2nd = 1'; }
}
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY s.last_name, s.first_name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="enrollments_' . ($school_year_id ? $school_year_id : 'all') . '_' . ($semester ?: 'all') . '.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['Scholar', 'Phone', 'Course', 'Year Level', 'School Year', 'Semester', 'Enrolled', 'Notes']);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fullname = trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
    fputcsv($output, [$fullname, $row['phone'], $row['course'], $row['year_level'], $row['school_year_label'], $row['semester'], $row['enrolled'] ? 'Yes' : 'No', $row['notes']]);
}
fclose($output);
exit;
?>

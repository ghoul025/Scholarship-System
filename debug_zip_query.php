<?php
require 'config.php';
$current_sy = $conn->query("SELECT label FROM school_years WHERE is_current = 1 LIMIT 1")->fetchColumn();
echo "Current SY: $current_sy\n";
$sql = "SELECT s.id, s.first_name, s.last_name, s.batch, s.scholarship_type, sy.label as school_year, d.file_path FROM scholars s INNER JOIN scholar_enrollments se ON se.scholar_id = s.id INNER JOIN school_years sy ON se.school_year_id = sy.id INNER JOIN documents d ON d.scholar_id = s.id WHERE sy.label = :current_sy GROUP BY d.id";
$stmt = $conn->prepare($sql);
$stmt->execute([':current_sy' => $current_sy]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

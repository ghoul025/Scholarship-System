<?php
/*
session_start();
require '../../config.php';
require '../../includes/school_years.php';
require '../../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden'); exit;
}

// Get all batches you want to export
$batches = !empty($_POST['batches']) ? array_map('trim', (array)$_POST['batches']) : [];

if (empty($batches)) {
    header('HTTP/1.1 400 Bad Request'); echo 'No batch selected'; exit;
}

$spreadsheet = new Spreadsheet();

// Loop over batches
foreach ($batches as $index => $batch) {
    // Create new sheet or use first sheet
    if ($index === 0) {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Batch $batch");
    } else {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle("Batch $batch");
    }

    // Fetch scholars for this batch
    $stmt = $pdo->prepare('SELECT s.*, u.username FROM scholars s JOIN users u ON s.user_id = u.id WHERE s.batch = ? ORDER BY s.last_name, s.first_name');
    $stmt->execute([$batch]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set header row
    $sheet->fromArray(['Username','Last Name','First Name','Middle Name','Phone','Course','Year Level','Units','Tuition Fee','Scholarship Type','Batch'], NULL, 'A1');

    // Fill data starting from row 2
    $rowNum = 2;
    foreach ($rows as $r) {
        $sheet->fromArray([
            $r['username'] ?? '',
            $r['last_name'] ?? '',
            $r['first_name'] ?? '',
            $r['middle_name'] ?? '',
            $r['phone'] ?? '',
            $r['course'] ?? '',
            $r['year_level'] ?? '',
            $r['units'] ?? '',
            $r['tuition_fee'] ?? '',
            $r['scholarship_type'] ?? '',
            $r['batch'] ?? ''
        ], NULL, 'A' . $rowNum);
        $rowNum++;
    }
}

// Output to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="payroll_batches_' . date('Ymd_His') . '.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
*/
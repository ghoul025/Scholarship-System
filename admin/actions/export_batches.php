<?php
session_start();
require '../../config.php';
require '../../vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden';
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Invalid CSRF token.';
    exit;
}

// Process batches and scholarship types
$batches_input = !empty($_POST['batches']) ? array_values(array_unique(array_map('trim', (array)$_POST['batches']))) : [];
if (empty($batches_input)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'No batch selected';
    exit;
}

// Log input batches for debugging
file_put_contents('debug.log', 'Input batches: ' . print_r($batches_input, true) . "\n", FILE_APPEND);

// Prepare spreadsheet
$spreadsheet = new Spreadsheet();
$sheet_index = 0;

foreach ($batches_input as $input) {
    // Parse input (e.g., "13.1|TES" -> batch: "13.1", scholarship_type: "TES")
    if (!preg_match('/^(\d+(?:\.\d+)?)\|(.+)$/', $input, $matches)) {
        file_put_contents('debug.log', "Invalid batch input format: $input\n", FILE_APPEND);
        continue;
    }
    $batch = $matches[1]; // Keep decimal places (e.g., "13.1")
    $scholarship_type = trim($matches[2]);

    // Clean batch: remove trailing .0 for cleaner look
    $batch = rtrim(rtrim($batch, '0'), '.');

    // Log parsed values
    file_put_contents('debug.log', "Parsed batch: $batch, Scholarship Type: $scholarship_type\n", FILE_APPEND);

    // Create sheet
    if ($sheet_index === 0) {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("$scholarship_type Batch $batch", 0, 31)); // Excel sheet title limit
    } else {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(substr("$scholarship_type Batch $batch", 0, 31));
    }
    $sheet_index++;

    // Headers
    $headers = ['Last Name', 'First Name', 'Middle Name', 'Course', 'Sex', 'Units', 'Tuition Fee'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Fetch scholars for this batch and scholarship type
    $stmt = $conn->prepare('
        SELECT s.last_name, s.first_name, s.middle_name, s.course, s.sex, s.units, s.tuition_fee
        FROM scholars s 
        WHERE s.scholarship_type = ? AND TRIM(CAST(s.batch AS CHAR)) = ?
        ORDER BY s.last_name, s.first_name
    ');
    $stmt->execute([$scholarship_type, $matches[1]]); // Use original batch for query
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log fetched data for debugging
    file_put_contents('debug.log', "Batch: $batch, Scholarship Type: $scholarship_type, Rows: " . count($rows) . "\nRaw data: " . print_r($rows, true) . "\n", FILE_APPEND);

    $rowNum = 2;
    foreach ($rows as $r) {
        // Log each row for debugging
        file_put_contents('debug.log', "Row $rowNum data: " . print_r($r, true) . "\n", FILE_APPEND);

        $sheet->fromArray([
            $r['last_name'] ?? 'N/A',
            $r['first_name'] ?? 'N/A',
            $r['middle_name'] ?? 'N/A',
            $r['course'] ?? 'N/A',
            $r['sex'] ?? 'N/A',
            $r['units'] ?? 'N/A',
            $r['tuition_fee'] ?? 'N/A'
        ], NULL, 'A' . $rowNum);
        $rowNum++;
    }

    // Auto-size columns
    foreach (range('A', $sheet->getHighestColumn()) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// Remove default sheet if empty and multiple sheets exist
if ($spreadsheet->getSheetCount() > 1 && $spreadsheet->getSheet(0)->getHighestRow() === 1) {
    $spreadsheet->removeSheetByIndex(0);
}

// Output
if ($sheet_index === 0) {
    header('HTTP/1.1 400 Bad Request');
    echo 'No data found for selected batches. Check debug.log for details.';
    exit;
}

$filename = 'exported_scholar_batches_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
<?php
session_start();
require '../../config.php';

// ✅ Load PhpSpreadsheet
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['scholar_ids'])) {
    $ids = array_filter(array_map('intval', explode(',', $_POST['scholar_ids'])));
    if (!$ids) {
        $_SESSION['error'] = 'No scholars selected for export.';
        header('Location: ../manage_scholars.php');
        exit;
    }

    // ✅ Log export action
    if (!is_dir('../../logs')) mkdir('../../logs', 0777, true);
    file_put_contents('../../logs/actions.log', date('Y-m-d H:i:s') . " - Exported selected scholars' credentials\n", FILE_APPEND);

    // ✅ Prepare spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header row
    $headers = ['Username', 'Full Name', 'Phone', 'Sex', 'Course', 'Year Level', 'Scholarship Type', 'Password'];
    $sheet->fromArray($headers, null, 'A1');

    // ✅ Fetch scholar data
    $in = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT u.username, s.first_name, s.middle_name, s.last_name, 
                   s.phone, s.sex, s.units, s.tuition_fee, s.course, 
                   s.year_level, s.scholarship_type, ec.password_plain
            FROM scholars s
            JOIN users u ON s.user_id = u.id
            JOIN exported_credentials ec ON ec.scholar_id = s.id
            WHERE s.id IN ($in)
            ORDER BY s.last_name ASC, s.first_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($ids);

    // ✅ Write rows
    $rowIndex = 2; // Start at row 2
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fullname = trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $sheet->fromArray([
            $row['username'],
            $fullname,
            $row['phone'],
            $row['sex'],
            $row['course'],
            $row['year_level'],
            $row['scholarship_type'],
            $row['password_plain']
        ], null, 'A' . $rowIndex);
        $rowIndex++;
    }

    // ✅ Auto-size columns for neatness
    foreach (range('A', $sheet->getHighestColumn()) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // ✅ Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="selected_scholars_account_info.xlsx"');
    header('Cache-Control: max-age=0');

    // ✅ Output to browser
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

$_SESSION['error'] = 'Invalid export request.';
header('Location: ../manage_scholars.php');
exit;

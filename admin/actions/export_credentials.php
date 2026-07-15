<?php
session_start();
require '../../config.php';
require '../../vendor/autoload.php'; // Ensure PhpSpreadsheet is installed via Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Restrict to admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Log export action
if (!is_dir('../../logs')) {
    mkdir('../../logs', 0777, true);
}
file_put_contents('../../logs/actions.log', date('Y-m-d H:i:s') . " - Exported all scholar credentials for password distribution\n", FILE_APPEND);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Scholar Credentials');

// Define headers
$headers = [
    'Username',
    'Full Name',
    'Phone',
    'Sex',
    'Course',
    'Year Level',
    'Scholarship Type',
    'Password'
];

// Set headers in the first row
$column = 'A';
foreach ($headers as $index => $header) {
    $sheet->setCellValue($column . '1', $header);
    $column++;
}

// Apply header styling
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

// Fetch scholar data
$sql = "SELECT u.username, s.first_name, s.middle_name, s.last_name, s.phone, s.sex, 
               s.course, s.year_level, s.scholarship_type, ec.password_plain
        FROM scholars s
        JOIN users u ON s.user_id = u.id
        JOIN exported_credentials ec ON ec.scholar_id = s.id
        ORDER BY s.last_name ASC, s.first_name ASC";
$stmt = $conn->query($sql);

// Populate data starting from row 2
$rowNumber = 2;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fullname = trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    $data = [
        $row['username'],
        $fullname,
        $row['phone'],
        $row['sex'],
        $row['course'],
        $row['year_level'],
        $row['scholarship_type'],
        $row['password_plain']
    ];

    $column = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($column . $rowNumber, $value);
        $column++;
    }
    $rowNumber++;
}

// Apply data styling
$dataStyle = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
];
$sheet->getStyle('A2:H' . ($rowNumber - 1))->applyFromArray($dataStyle);

// Auto-size columns for better readability
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set headers for Excel download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="all_scholar_credentials.xlsx"');
header('Cache-Control: max-age=0');

// Write and output the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
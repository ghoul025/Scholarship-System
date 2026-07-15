<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../config.php';
require '../../vendor/autoload.php'; // PHPSpreadsheet via Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

session_start();

// Handle selected TES applications
$tes_ids = isset($_POST['tes_ids']) && is_array($_POST['tes_ids']) ? array_map('trim', $_POST['tes_ids']) : [];
if (empty($tes_ids)) {
    $_SESSION['batch_error'] = "No applications selected for export.";
    header("Location: ../manage_applications.php?tab=tes");
    exit;
}

// Validate and sanitize tes_ids
$tes_ids = array_filter($tes_ids, function($id) {
    return preg_match('/^[A-Za-z0-9-]+$/', $id); // Allow alphanumeric and hyphens
});
if (empty($tes_ids)) {
    $_SESSION['batch_error'] = "Invalid application IDs provided.";
    header("Location: ../manage_applications.php?tab=tes");
    exit;
}

// Fetch selected TES applications
try {
    $placeholders = implode(',', array_fill(0, count($tes_ids), '?'));
    $sql_tes = "SELECT * FROM tes_applicants WHERE student_id IN ($placeholders) ORDER BY last_name ASC, given_name ASC";
    $stmt_tes = $conn->prepare($sql_tes);
    $stmt_tes->execute($tes_ids);
    $tes_apps = $stmt_tes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['batch_error'] = "Database error: " . $e->getMessage();
    header("Location: ../manage_applications.php?tab=tes");
    exit;
}

if (empty($tes_apps)) {
    $_SESSION['batch_error'] = "No matching applications found.";
    header("Location: ../manage_applications.php?tab=tes");
    exit;
}

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Annex 1');

// Function to convert column index to Excel column letter
function getColumnLetter($colIndex) {
    $letter = '';
    while ($colIndex > 0) {
        $mod = ($colIndex - 1) % 26;
        $letter = chr(65 + $mod) . $letter;
        $colIndex = (int)(($colIndex - $mod) / 26);
    }
    return $letter;
}

// Set header rows
$headers = [
    1 => ['LIST OF TES APPLICANTS'],
    2 => array_merge(['STUDENT INFORMATION'], array_fill(0, 9, null), ['FAMILY BACKGROUND'], array_fill(0, 12, null)),
    3 => [
        'SEQ', 'STUDENT ID', "STUDENT'S NAME", null, null, null, "STUDENT'S PROFILE", null, null, null,
        "FATHER'S NAME", null, null, "MOTHER'S MAIDEN NAME", null, null, 'PERMANENT ADDRESS', null,
        'DISABILITY (leave blank if NOT Applicable)', 'CONTACT NUMBER', 'EMAIL ADDRESS',
        'INDIGENOUS PEOPLE GROUP (leave blank if NOT Applicable)', null, null
    ],
    4 => [
        null, null, 'LAST NAME', 'GIVEN NAME', 'EXT. NAME', 'MIDDLE NAME', 'SEX (Male or Female)', 'BIRTHDATE (dd/mm/yyyy)',
        'COMPLETE PROGRAM NAME (Should be consistent with your HEI Registry)', 'YEAR LEVEL (1,2,3,4,5)',
        'LAST NAME', 'GIVEN NAME', 'MIDDLE NAME', 'LAST NAME', 'GIVEN NAME', 'MIDDLE NAME',
        'STREET & BARANGAY', 'ZIPCODE (TES Applicant)', null, null, null, null, null, null
    ]
];

// Track maximum width for each column (in characters)
$maxWidths = array_fill(1, 24, 0);

// Set header rows and calculate header widths
foreach ($headers as $rowNum => $rowData) {
    $col = 1;
    foreach ($rowData as $cellValue) {
        if ($cellValue !== null) {
            $sheet->setCellValue(getColumnLetter($col) . $rowNum, $cellValue);
            $maxWidths[$col] = max($maxWidths[$col], strlen($cellValue));
        }
        $col++;
    }
}

// Merge cells for header rows
$sheet->mergeCells('A1:X1'); // Merge "LIST OF TES APPLICANTS" across columns A-X
$sheet->mergeCells('A2:J2'); // Merge "STUDENT INFORMATION" over columns A-J
$sheet->mergeCells('K2:M2'); // Merge "FAMILY BACKGROUND" over columns K-M
$sheet->mergeCells('N2:P2'); // Merge "MOTHER'S MAIDEN NAME" placeholder
$sheet->mergeCells('A3:A4'); // Merge SEQ
$sheet->mergeCells('B3:B4'); // Merge STUDENT ID
$sheet->mergeCells('C3:F3'); // Merge "STUDENT'S NAME"
$sheet->mergeCells('G3:J3'); // Merge "STUDENT'S PROFILE"
$sheet->mergeCells('K3:M3'); // Merge "FATHER'S NAME"
$sheet->mergeCells('N3:P3'); // Merge "MOTHER'S MAIDEN NAME"
$sheet->mergeCells('Q3:R3'); // Merge "PERMANENT ADDRESS"
$sheet->mergeCells('S3:S4'); // Merge DISABILITY
$sheet->mergeCells('T3:T4'); // Merge CONTACT NUMBER
$sheet->mergeCells('U3:U4'); // Merge EMAIL ADDRESS
$sheet->mergeCells('V3:V4'); // Merge INDIGENOUS PEOPLE GROUP
$sheet->mergeCells('W3:X4'); // Merge empty columns

// Data rows starting from row 5
$rowNum = 5;
$seq = 1;
foreach ($tes_apps as $app) {
    $birth = new DateTime($app['birthdate']);
    $birth_formatted = $birth->format('d/m/Y');
    $rowData = [
        $seq,
        $app['student_id'],
        $app['last_name'],
        $app['given_name'],
        $app['extension_name'],
        $app['middle_name'],
        $app['sex'],
        $birth_formatted,
        $app['complete_program_name'],
        $app['year_level'],
        $app['father_last_name'],
        $app['father_given_name'],
        $app['father_middle_name'],
        $app['mother_last_name'],
        $app['mother_given_name'],
        $app['mother_middle_name'],
        $app['street_barangay'],
        $app['zip_code'],
        $app['disability'],
        $app['contact_number'],
        $app['email_address'],
        $app['indigenous_people_group'],
        null,
        null
    ];
    $col = 1;
    foreach ($rowData as $cellValue) {
        if ($cellValue !== null) {
            $sheet->setCellValue(getColumnLetter($col) . $rowNum, $cellValue);
            $maxWidths[$col] = max($maxWidths[$col], strlen((string)$cellValue));
        }
        $col++;
    }
    $rowNum++;
    $seq++;
}

// Set column widths based on maximum content length
$minWidth = 5; // Minimum column width in characters
$charWidth = 1.1; // Approximate width per character for Arial 10 (in Excel units)
for ($col = 1; $col <= 24; $col++) {
    $columnLetter = getColumnLetter($col);
    $width = max($minWidth, $maxWidths[$col] * $charWidth);
    $sheet->getColumnDimension($columnLetter)->setWidth($width);
}

// Apply styling
$styleArray = [
    'font' => [
        'name' => 'Arial',
        'size' => 10,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];

// Center-align specific headers
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('K2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Apply style to all cells
$highestRow = $sheet->getHighestRow();
$sheet->getStyle('A1:' . getColumnLetter(24) . $highestRow)->applyFromArray($styleArray);

// Bold headers in rows 1-4
$sheet->getStyle('A1:' . getColumnLetter(24) . '4')->getFont()->setBold(true);

// Freeze header rows
$sheet->freezePane('A5');

// Output to browser with timestamp in filename
$timestamp = date('Y-m-d_H-i-s');
$filename = "Annex_1_List_of_TES_Applicants_$timestamp.xlsx";
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');
try {
    $writer->save('php://output');
} catch (Exception $e) {
    $_SESSION['batch_error'] = "Export failed: " . $e->getMessage();
    header("Location: ../manage_applications.php?tab=tes");
    exit;
}
exit;
?>
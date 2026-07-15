<?php
// download_all_credentials.php
// Download all credentials grouped by year, scholarship type, batch, and student

session_start();
require '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden';
    exit;
}


// Get the current school year label
$current_sy = $conn->query("SELECT label FROM school_years WHERE is_current = 1 LIMIT 1")->fetchColumn();
if (!$current_sy) {
    die('No current school year set.');
}

$zip = new ZipArchive();
$filename = sys_get_temp_dir() . '/scholar_credentials_' . date('Ymd_His') . '.zip';
if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die('Could not create zip file');
}

// Query all scholars with their info

$sql = "
    SELECT s.id, s.first_name, s.last_name, s.batch, s.scholarship_type, sy.label as school_year, d.*
    FROM scholars s
    INNER JOIN scholar_enrollments se ON se.scholar_id = s.id
    INNER JOIN school_years sy ON se.school_year_id = sy.id
    INNER JOIN documents d ON d.scholar_id = s.id
    WHERE sy.label = :current_sy
    GROUP BY d.id
";
$stmt = $conn->prepare($sql);
$stmt->execute([':current_sy' => $current_sy]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by scholar for folder structure
$grouped = [];
foreach ($rows as $row) {
    $sid = $row['id'];
    if (!isset($grouped[$sid])) {
        $grouped[$sid] = [
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'batch' => $row['batch'],
            'scholarship_type' => $row['scholarship_type'],
            'school_year' => $row['school_year'],
            'docs' => []
        ];
    }
    $grouped[$sid]['docs'][] = $row;
}

foreach ($grouped as $sid => $scholar) {
    $sy = $scholar['school_year'] ?: 'UnknownYear';
    $stype = $scholar['scholarship_type'] ?: 'UnknownType';
    $batch = $scholar['batch'] !== null ? rtrim(rtrim($scholar['batch'], '0'), '.') : 'UnknownBatch';
    $student = $scholar['last_name'] . '_' . $scholar['first_name'] . '_ID' . $sid;
    $basePath = "$sy/$stype/Batch_$batch/$student/";
    foreach ($scholar['docs'] as $doc) {
        // Fix path: replace 'uploads/' with 'Uploads/' for correct case
        $fixedPath = preg_replace('#^uploads/#i', 'Uploads/', $doc['file_path']);
        $filePath = realpath(__DIR__ . '/../../' . $fixedPath);
        if ($filePath && file_exists($filePath)) {
            $label = $doc['document_type'] ?: 'document';
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $zipName = $basePath . $label . '.' . $ext;
            error_log("Adding to zip: $filePath as $zipName");
            $zip->addFile($filePath, $zipName);
        } else {
            error_log("Missing file: $fixedPath (resolved: $filePath)");
        }
    }
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="scholar_credentials_' . date('Ymd_His') . '.zip"');
header('Content-Length: ' . filesize($filename));
readfile($filename);
unlink($filename);
exit;

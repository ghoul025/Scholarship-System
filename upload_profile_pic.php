<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'scholar') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
    exit;
}

$file = $_FILES['profile_pic'];
$allowed_types = ['image/jpeg', 'image/png'];
$max_size = 2 * 1024 * 1024; // 2MB

// Validate MIME using finfo (more reliable than client-supplied $file['type'])
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!in_array($mime, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG and PNG allowed.']);
    exit;
}
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 2MB.']);
    exit;
}

// Validate image dimensions

// Auto resize if needed
list($width, $height) = getimagesize($file['tmp_name']);
if ($width > 512 || $height > 512) {
    $max_dim = 512;
    $ratio = min($max_dim / $width, $max_dim / $height);
    $new_width = (int)($width * $ratio);
    $new_height = (int)($height * $ratio);
    $src_img = null;
    if ($file['type'] === 'image/jpeg') {
        $src_img = imagecreatefromjpeg($file['tmp_name']);
    } elseif ($file['type'] === 'image/png') {
        $src_img = imagecreatefrompng($file['tmp_name']);
    }
    if ($src_img) {
        $dst_img = imagecreatetruecolor($new_width, $new_height);
        // Preserve transparency for PNG
        if ($file['type'] === 'image/png') {
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
        }
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        // Overwrite tmp file with resized image
        if ($file['type'] === 'image/jpeg') {
            imagejpeg($dst_img, $file['tmp_name'], 90);
        } elseif ($file['type'] === 'image/png') {
            imagepng($dst_img, $file['tmp_name'], 6);
        }
        imagedestroy($src_img);
        imagedestroy($dst_img);
    }
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$profile_pic_folder = 'uploads/profile_pics/';
if (!is_dir($profile_pic_folder)) {
    mkdir($profile_pic_folder, 0777, true);
}
$filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
$target_path = $profile_pic_folder . $filename;

if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
    exit;
}

// Update DB
$stmt = $conn->prepare('UPDATE scholars SET profile_pic = ? WHERE user_id = ?');
$stmt->execute([$target_path, $user_id]);

// Remove old profile pic if exists and is different
$stmt_old = $conn->prepare('SELECT profile_pic FROM scholars WHERE user_id = ?');
$stmt_old->execute([$user_id]);
$old_pic = $stmt_old->fetchColumn();
if ($old_pic && $old_pic !== $target_path && file_exists($old_pic)) {
    @unlink($old_pic);
}

echo json_encode(['success' => true, 'new_pic' => $target_path]);
exit;

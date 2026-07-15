<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'scholar') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_upload'])) {
    $response = ['success' => false, 'message' => 'Upload failed.'];

    // Validate document type
    if (!isset($_POST['document_type']) || empty($_POST['document_type'])) {
        $response['message'] = 'Document type is missing.';
        echo json_encode($response); exit;
    }

    // Validate file upload
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'No file uploaded or error occurred.';
        echo json_encode($response); exit;
    }

    $file = $_FILES['document'];
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file['type'], $allowed_types)) {
        $response['message'] = 'Invalid file type. Only PDF, JPG, and PNG allowed.';
        echo json_encode($response); exit;
    }

    if ($file['size'] > $max_size) {
        $response['message'] = 'File too large. Max 5MB.';
        echo json_encode($response); exit;
    }

    // Ensure upload directory exists
    $upload_dir = 'uploads/credentials/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // Generate safe unique filename
    $filename = 'doc_' . $_SESSION['user_id'] . '_' . time() . '_' .
        preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file['name']);
    $target_file = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        $response['message'] = 'Failed to move uploaded file.';
        echo json_encode($response); exit;
    }

    // Get scholar ID
    $user_id = $_SESSION['user_id'];
    $stmt_scholar = $conn->prepare("SELECT id FROM scholars WHERE user_id = ?");
    $stmt_scholar->execute([$user_id]);
    $scholar = $stmt_scholar->fetch(PDO::FETCH_ASSOC);

    if ($scholar && isset($scholar['id'])) {
        $scholar_id = $scholar['id'];

        // Insert or update document
        $stmt = $conn->prepare("
            INSERT INTO documents (scholar_id, document_type, file_path, status)
            VALUES (?, ?, ?, 'pending')
            ON DUPLICATE KEY UPDATE
                file_path = VALUES(file_path),
                status = 'pending'
        ");
        $stmt->execute([$scholar_id, $_POST['document_type'], $target_file]);

        $response = ['success' => true, 'message' => 'Document uploaded successfully and set to Pending!'];
    } else {
        $response['message'] = 'Scholar not found.';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

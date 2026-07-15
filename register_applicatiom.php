<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize inputs
        $username       = trim($_POST['username'] ?? '');
        $first_name     = trim($_POST['first_name'] ?? '');
        $middle_name    = trim($_POST['middle_name'] ?? '');
        $last_name      = trim($_POST['last_name'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $sex            = trim($_POST['sex'] ?? '');
        $units          = intval($_POST['units'] ?? 0);
        $tuition_fee    = floatval($_POST['tuition_fee'] ?? 0);
        $course         = trim($_POST['course'] ?? '');
        $year_level     = trim($_POST['year_level'] ?? '');
        $scholarship_type = trim($_POST['scholarship_type'] ?? '');
        $batch_input    = trim($_POST['batch'] ?? '');

        // Validation
        if (
            !$username || !$first_name || !$last_name || !$phone ||
            !$sex || !$units || !$tuition_fee || !$course ||
            !$year_level || !$scholarship_type
        ) {
            throw new Exception("All required fields must be filled.");
        }

        // Phone validation (Philippines: 11 digits, starts with 09)
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            throw new Exception("Invalid phone number format. Example: 09123456789");
        }

        // Batch validation (optional, must be number or decimal with up to 2 places)
        $batch = null;
        if ($batch_input !== '') {
            if (!preg_match('/^\d+(\.\d{1,2})?$/', $batch_input)) {
                throw new Exception("Batch must be a number (e.g., 13 or 13.5).");
            }
            $batch = number_format((float)$batch_input, 2, '.', '');
        }

        // Prevent Duplicate Applications
        $check = $conn->prepare("SELECT id FROM scholar_applications WHERE username = ? OR phone = ? LIMIT 1");
        $check->execute([$username, $phone]);
        if ($check->fetch()) {
            throw new Exception("An application with this username or phone number already exists.");
        }

        // Insert Application
        $stmt = $conn->prepare("
            INSERT INTO scholar_applications 
            (username, first_name, middle_name, last_name, phone, sex, units, tuition_fee, course, year_level, scholarship_type, batch) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $username, $first_name, $middle_name ?: null, $last_name,
            $phone, $sex, $units, $tuition_fee, $course,
            $year_level, $scholarship_type, $batch
        ]);

        $_SESSION['application_message'] = "✅ Your application has been submitted and is pending admin approval.";
        $_SESSION['application_status'] = "success";

    } catch (Exception $e) {
        $_SESSION['application_message'] = "❌ " . $e->getMessage();
        $_SESSION['application_status'] = "error";
    }

    header("Location: index.php");
    exit;
}

$_SESSION['application_message'] = "❌ Invalid registration attempt.";
$_SESSION['application_status'] = "error";
header("Location: index.php");
exit;
?>
<?php
session_start();
require '../../config.php';
require '../../includes/school_years.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
try {
    switch ($action) {
        case 'create':
            $label = trim($_POST['label'] ?? '');
            $start = $_POST['start_date'] ?: null;
            $end = $_POST['end_date'] ?: null;
            $is_current = isset($_POST['is_current']) ? 1 : 0;
            if (!$label) throw new Exception('Label required');
            createSchoolYear($label, $start, $end, $is_current);
            $_SESSION['sy_message'] = 'School year created.';
            break;
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $label = trim($_POST['label'] ?? '');
            $start = $_POST['start_date'] ?: null;
            $end = $_POST['end_date'] ?: null;
            $is_current = isset($_POST['is_current']) ? 1 : 0;
            if (!$id || !$label) throw new Exception('Invalid data');
            updateSchoolYear($id, $label, $start, $end, $is_current);
            $_SESSION['sy_message'] = 'School year updated.';
            break;
        case 'set_current':
            $id = intval($_POST['id'] ?? 0);
            if ($id) setCurrentSchoolYear($id);
            $_SESSION['sy_message'] = 'Current school year set.';
            break;
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id) deleteSchoolYear($id);
            $_SESSION['sy_message'] = 'School year deleted.';
            break;
        default:
            throw new Exception('Unknown action');
    }
} catch (Exception $e) {
    $_SESSION['sy_error'] = $e->getMessage();
}
header('Location: ../manage_school_years.php');
exit;

?>

<?php
session_start();
require '../../config.php';
require '../../includes/school_years.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scholar_id'])) {
    try {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $id = intval($_POST['scholar_id']);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!function_exists('to_title_case')) {
            function to_title_case($s) {
                $s = trim((string)$s);
                if ($s === '') return '';
                if (function_exists('mb_convert_case')) return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
                return ucwords(strtolower($s));
            }
        }

        $first_name = to_title_case($_POST['first_name'] ?? '');
        $middle_name = to_title_case($_POST['middle_name'] ?? '');
        $last_name = to_title_case($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $sex = trim($_POST['sex'] ?? '');
        $units = intval($_POST['units'] ?? 0);
        $tuition_fee = floatval($_POST['tuition_fee'] ?? 0);
        $course = trim($_POST['course'] ?? '');
        $year_level = intval($_POST['year_level'] ?? 0);
        $scholarship_type = trim($_POST['scholarship_type'] ?? '');
        $batch_input = trim($_POST['batch'] ?? '');
        $batch = ($batch_input === '') ? null : floatval($batch_input);
        $status = $_POST['status'] ?? null;
        $school_year_id = intval($_POST['school_year_id'] ?? 0);
        $semester = $_POST['semester'] ?? null;

        // Fallbacks for NOT NULL columns
        if ($first_name === '') $first_name = 'Unknown';
        if ($last_name === '') $last_name = 'Unknown';
        if ($course === '') $course = 'Undeclared';
        if ($year_level === 0) $year_level = 1;
        if ($scholarship_type === '') $scholarship_type = 'General';

        $conn->beginTransaction();

        // 1) Get user_id from scholars
        $stmt = $conn->prepare('SELECT user_id FROM scholars WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Scholar not found.");
        }

        $user_id = $row['user_id'];

        // 2) Update users.username only (email is stored on scholars table)
        if ($username !== '') {
            $stmt = $conn->prepare('UPDATE users SET username = ? WHERE id = ?');
            $stmt->execute([$username, $user_id]);
        }

        // Basic email validation: allow empty to skip (we'll persist to scholars.email)
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        // 3) Update scholars
        $stmt = $conn->prepare('UPDATE scholars SET 
            first_name = ?, 
            middle_name = ?, 
            last_name = ?, 
            phone = ?, 
            sex = ?, 
            units = ?, 
            tuition_fee = ?, 
            course = ?, 
            year_level = ?, 
            scholarship_type = ?, 
            batch = ?, 
            email = ? 
            WHERE id = ?'
        );
        $stmt->execute([
            $first_name,
            $middle_name !== '' ? $middle_name : null,
            $last_name,
            $phone !== '' ? $phone : null,
            $sex !== '' ? $sex : null,
            $units,
            $tuition_fee,
            $course,
            $year_level,
            $scholarship_type,
            $batch,
            $email !== '' ? $email : null,
            $id
        ]);

        // 4) Update scholar status if provided
        if ($status) {
            setScholarStatus($id, $status);
        }

        // 5) Optionally enroll without duplicates
        $enrollMessage = '';
        if ($school_year_id && $semester) {
            // Check existing enrollment row for this scholar + school year
            $stmt = $conn->prepare('SELECT id, enrolled_1st, enrolled_2nd FROM scholar_enrollments WHERE scholar_id = ? AND school_year_id = ? LIMIT 1');
            $stmt->execute([$id, $school_year_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $alreadyEnrolled = false;
            if ($row) {
                $flag = $semester === '1st' ? (!empty($row['enrolled_1st'])) : (!empty($row['enrolled_2nd']));
                $alreadyEnrolled = $flag ? true : false;
            }

            if (!$alreadyEnrolled) {
                // Use helper which inserts or updates the appropriate semester flag
                $ok = enrollScholar($id, $school_year_id, $semester, $status === 'enrolled', 'Manual edit enrollment');
                $enrollMessage = $ok ? ' Enrollment done successfully.' : ' Enrollment failed.';
            } else {
                $enrollMessage = ' Scholar was already enrolled for this semester.';
            }
        }

        // 6) Update exported_credentials if column exists
        try {
            $stmt = $conn->prepare('UPDATE exported_credentials SET username = ? WHERE scholar_id = ?');
            $stmt->execute([$username, $id]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Unknown column') === false) {
                throw $e;
            }
        }

        $conn->commit();

        $_SESSION['message'] = 'Scholar updated successfully.' . $enrollMessage;

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = 'Update failed: ' . $e->getMessage();
    }

    header('Location: ../manage_scholars.php');
    exit;
}

header('Location: ../manage_scholars.php');
exit;
?>
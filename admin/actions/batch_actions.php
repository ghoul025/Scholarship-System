<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/audit_log.php';
require_once __DIR__ . '/../../includes/school_years.php';

if (session_status() === PHP_SESSION_NONE) session_start();
file_put_contents('logs/batch_actions.log', "batch_actions.php started: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Restrict to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    file_put_contents('logs/batch_actions.log', "Unauthorized access: user_id=" . ($_SESSION['user_id'] ?? 'none') . "\n", FILE_APPEND);
    header('Location: ../login.php');
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    $_SESSION['batch_error'] = 'Invalid CSRF token.';
    header('Location: ../manage_scholars.php');
    exit;
}

// Collect scholar IDs
$ids = [];
if (!empty($_POST['scholar_ids']) && is_array($_POST['scholar_ids'])) {
    $ids = array_map('intval', $_POST['scholar_ids']);
} elseif (!empty($_POST['batch_edit_ids'])) {
    $ids = array_map('intval', array_filter(array_map('trim', explode(',', $_POST['batch_edit_ids']))));
}

if (empty($ids)) {
    $_SESSION['batch_error'] = 'No scholars selected.';
    header('Location: ../manage_scholars.php');
    exit;
}

$action = $_POST['batch_action'] ?? '';

/**
 * Enroll a scholar in a semester for a given school year.
 */
if (!function_exists('enrollScholar')) {
    function enrollScholar($scholar_id, $school_year_id, $semester, $remarks = 'Batch enrollment') {
        global $conn;

        // Determine which column to enroll in, and which to clear
        $col_enroll = $semester === '1st' ? 'enrolled_1st' : 'enrolled_2nd';
        $col_clear  = $semester === '1st' ? 'enrolled_2nd' : 'enrolled_1st';

        try {
            // Check if row exists
            $stmt = $conn->prepare("SELECT id, enrolled_1st, enrolled_2nd FROM scholar_enrollments WHERE scholar_id = ? AND school_year_id = ?");
            $stmt->execute([$scholar_id, $school_year_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // If already enrolled in this semester, skip
                if ($row[$col_enroll]) return false;

                // Enroll in this semester and clear the other
                $stmt = $conn->prepare("UPDATE scholar_enrollments SET $col_enroll = 1, $col_clear = 0, updated_at = NOW() WHERE id = ?");
                return $stmt->execute([$row['id']]);
            } else {
                // Insert new row
                $en1 = ($semester === '1st') ? 1 : 0;
                $en2 = ($semester === '2nd') ? 1 : 0;
                $stmt = $conn->prepare("INSERT INTO scholar_enrollments (scholar_id, school_year_id, enrolled_1st, enrolled_2nd, notes, updated_at) VALUES (?, ?, ?, ?, ?, NOW())");
                return $stmt->execute([$scholar_id, $school_year_id, $en1, $en2, $remarks]);
            }
        } catch (PDOException $e) {
            file_put_contents('logs/batch_actions.log', "Enroll error for scholar $scholar_id: " . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }
}

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch ($action) {
        case 'reset':
            $hash = password_hash('123456', PASSWORD_DEFAULT);
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("SELECT user_id FROM scholars WHERE id IN ($in)");
            $stmt->execute($ids);
            $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($user_ids) {
                $in2 = implode(',', array_fill(0, count($user_ids), '?'));
                $stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE id IN ($in2)");
                $stmt2->execute(array_merge([$hash], $user_ids));
                audit_log($_SESSION['user_id'], 'batch_reset_passwords', json_encode(['ids' => $ids, 'user_ids' => $user_ids]));
                $_SESSION['batch_message'] = 'Passwords reset to default for selected scholars.';
            } else {
                $_SESSION['batch_error'] = 'No user accounts found for selected scholars.';
            }
            break;

        case 'delete':
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("SELECT user_id FROM scholars WHERE id IN ($in)");
            $stmt->execute($ids);
            $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete related records
            $conn->prepare("DELETE FROM exported_credentials WHERE scholar_id IN ($in)")->execute($ids);
            $conn->prepare("DELETE FROM scholar_enrollments WHERE scholar_id IN ($in)")->execute($ids);
            $conn->prepare("DELETE FROM scholars WHERE id IN ($in)")->execute($ids);

            if ($user_ids) {
                $in2 = implode(',', array_fill(0, count($user_ids), '?'));
                $conn->prepare("DELETE FROM users WHERE id IN ($in2)")->execute($user_ids);
            }

            audit_log($_SESSION['user_id'], 'batch_delete_scholars', json_encode(['ids' => $ids, 'user_ids' => $user_ids]));
            $_SESSION['batch_message'] = 'Selected scholars deleted successfully.';
            break;

        case 'change_year':
        case 'change_course':
        case 'change_type':
        case 'assign_batch':
            $map = [
                'change_year' => ['column' => 'year_level', 'valid' => ['1st Year','2nd Year','3rd Year','4th Year'], 'post' => 'new_year_level'],
                'change_course' => ['column' => 'course', 'valid' => ['BSCS','BSA','BSHM','BSBA','BSTM','BEED','BSED'], 'post' => 'new_course'],
                'change_type' => ['column' => 'scholarship_type', 'valid' => ['TES','TDP','Listahanan'], 'post' => 'new_scholarship_type'],
                'assign_batch' => ['column' => 'batch', 'valid' => null, 'post' => 'new_batch']
            ];

            $col = $map[$action]['column'];
            $val_input = trim($_POST[$map[$action]['post']] ?? '');

            if ($action === 'assign_batch') {
                if ($val_input === '') {
                    $_SESSION['batch_error'] = 'Batch number is required.';
                    break;
                }
                if (!preg_match('/^\d+(\.\d{1,2})?$/', $val_input)) {
                    $_SESSION['batch_error'] = 'Batch must be a number or decimal with up to 2 decimal places (e.g., 13 or 13.5).';
                    break;
                }
                $val_input = number_format((float)$val_input, 2, '.', '');
            } elseif ($map[$action]['valid'] && !in_array($val_input, $map[$action]['valid']) || $val_input === '') {
                $_SESSION['batch_error'] = 'Invalid or missing value for ' . $col;
                break;
            }

            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("UPDATE scholars SET $col = ? WHERE id IN ($in)");
            $stmt->execute(array_merge([$val_input], $ids));

            audit_log($_SESSION['user_id'], $action, json_encode(['ids' => $ids, $col => $val_input]));
            $_SESSION['batch_message'] = ucfirst(str_replace('_',' ',$col)) . ' updated for selected scholars.';
            break;

        case 'enroll':
            $school_year_id = intval($_POST['school_year_id'] ?? 0);
            $semester = $_POST['semester'] ?? '';
            if (!$school_year_id || !in_array($semester, ['1st','2nd'])) {
                $_SESSION['batch_error'] = 'Invalid school year or semester.';
                break;
            }

            $enrolled = $skipped = [];
            foreach ($ids as $sid) {
                $ok = enrollScholar($sid, $school_year_id, $semester);
                $ok ? $enrolled[] = $sid : $skipped[] = $sid;
            }

            audit_log($_SESSION['user_id'], 'batch_enroll', json_encode([
                'enrolled'=>$enrolled,'skipped'=>$skipped,
                'school_year_id'=>$school_year_id,'semester'=>$semester
            ]));

            $_SESSION['batch_message'] = count($enrolled) . ' scholar(s) enrolled successfully.';
            if ($skipped) $_SESSION['batch_error'] = count($skipped) . ' scholar(s) skipped (already enrolled).';
            break;

        default:
            $_SESSION['batch_error'] = 'Invalid or missing batch action.';
            break;
    }

} catch (PDOException $e) {
    $_SESSION['batch_error'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['batch_error'] = 'Error: ' . $e->getMessage();
}

header('Location: ../manage_scholars.php');
exit;
?>
<?php
session_start();
require '../../config.php';
require '../../includes/school_years.php';
header('Content-Type: application/json');

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get POST data
$action = $_POST['action'] ?? '';

try {
    if ($action === 'toggle') {
        // Validate required inputs for toggling
        $scholar_id = intval($_POST['scholar_id'] ?? 0);
        $semester   = $_POST['semester'] ?? '';
        $school_year_id = intval($_POST['school_year_id'] ?? 0);

        if (!$scholar_id || !$semester || !$school_year_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required data (scholar, semester, or school year)']);
            exit;
        }

            // Normalize semester -> column
            $col = $semester === '1st' ? 'enrolled_1st' : ($semester === '2nd' ? 'enrolled_2nd' : null);
            if (!$col) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid semester']);
                exit;
            }

            // Check if an enrollment row exists for this scholar + school year
            $stmt = $pdo->prepare('SELECT id, enrolled_1st, enrolled_2nd FROM scholar_enrollments WHERE scholar_id = ? AND school_year_id = ?');
            $stmt->execute([$scholar_id, $school_year_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Toggle enrolled flag but enforce exclusivity: if setting desired=1, clear the other flag
            $current = !empty($row[$col]) ? 1 : 0;
            $new = $current ? 0 : 1;
            $other_col = $col === 'enrolled_1st' ? 'enrolled_2nd' : 'enrolled_1st';

            if ($new === 1) {
                // set requested semester to 1 and clear the other
                $stmt = $pdo->prepare("UPDATE scholar_enrollments SET $col = 1, $other_col = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$row['id']]);
            } else {
                // just clear this semester flag
                $stmt = $pdo->prepare("UPDATE scholar_enrollments SET $col = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$row['id']]);
            }
        } else {
            // Insert new enrollment if none exists — when inserting, only set requested semester flag
            $enrolled_1st = ($semester === '1st') ? 1 : 0;
            $enrolled_2nd = ($semester === '2nd') ? 1 : 0;
            $stmt = $pdo->prepare('INSERT INTO scholar_enrollments (scholar_id, school_year_id, enrolled_1st, enrolled_2nd, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)');
            $stmt->execute([$scholar_id, $school_year_id, $enrolled_1st, $enrolled_2nd]);
            $new = ($semester === '1st' ? $enrolled_1st : $enrolled_2nd);
        }

        echo json_encode(['ok' => true, 'enrolled' => (bool)$new]);
        exit;

    } elseif ($action === 'notes') {
        // Update notes for a specific enrollment
        $enrollment_id = intval($_POST['id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        if (!$enrollment_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing enrollment id']);
            exit;
        }

        $stmt = $pdo->prepare('UPDATE scholar_enrollments SET notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$notes, $enrollment_id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    throw new Exception('Unknown action');

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

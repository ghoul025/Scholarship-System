<?php
// Modular helper for school years and scholar enrollments
// Usage: include __DIR__ . '/school_years.php';

function get_db() {
    global $conn;
    // If $conn isn't available, attempt to load the project's config (best-effort)
    if (!isset($conn) || $conn === null) {
        $possible = [
            __DIR__ . '/../config.php',      // include relative to includes/
            __DIR__ . '/../../config.php'    // fallback: two levels up
        ];
        foreach ($possible as $p) {
            if (file_exists($p)) {
                require_once $p;
                break;
            }
        }
    }
    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection ($conn) is not available. Ensure config.php is required before including school_years.php');
    }
    return $conn;
}

function listSchoolYears() {
    $db = get_db();
    $stmt = $db->query("SELECT * FROM school_years ORDER BY start_date DESC, id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCurrentSchoolYear() {
    $db = get_db();
    $stmt = $db->query("SELECT * FROM school_years WHERE is_current = 1 LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return $row;
    // fallback: latest by start_date
    $stmt = $db->query("SELECT * FROM school_years ORDER BY start_date DESC, id DESC LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function setCurrentSchoolYear($id) {
    $db = get_db();
    $db->prepare("UPDATE school_years SET is_current = 0")->execute();
    $stmt = $db->prepare("UPDATE school_years SET is_current = 1 WHERE id = ?");
    return $stmt->execute([$id]);
}

// Create a school year. $label e.g. "2024-2025". start_date/end_date optional.
function createSchoolYear($label, $start_date = null, $end_date = null, $is_current = 0) {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO school_years (label, start_date, end_date, is_current) VALUES (?, ?, ?, ?)");
    $ok = $stmt->execute([$label, $start_date ?: null, $end_date ?: null, $is_current ? 1 : 0]);
    if ($ok && $is_current) {
        // ensure only one current
        $id = $db->lastInsertId();
        setCurrentSchoolYear($id);
    }
    return $ok;
}

function updateSchoolYear($id, $label, $start_date = null, $end_date = null, $is_current = 0) {
    $db = get_db();
    if ($is_current) {
        setCurrentSchoolYear($id);
    }
    $stmt = $db->prepare("UPDATE school_years SET label = ?, start_date = ?, end_date = ?, is_current = ? WHERE id = ?");
    return $stmt->execute([$label, $start_date ?: null, $end_date ?: null, $is_current ? 1 : 0, $id]);
}

function deleteSchoolYear($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM school_years WHERE id = ?");
    return $stmt->execute([$id]);
}

if (!function_exists('enrollScholar')) {
    function enrollScholar($scholar_id, $school_year_id, $semester = '1st', $enrolled = 1, $notes = null) {
        $db = get_db();
        $col = $semester === '1st' ? 'enrolled_1st' : ($semester === '2nd' ? 'enrolled_2nd' : null);
        if (!$col) return false;

        $stmt = $db->prepare('SELECT id, enrolled_1st, enrolled_2nd FROM scholar_enrollments WHERE scholar_id = ? AND school_year_id = ? LIMIT 1');
        $stmt->execute([$scholar_id, $school_year_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        try {
            if ($row) {
                $current = !empty($row[$col]) ? 1 : 0;
                $desired = $enrolled ? 1 : 0;
                if ($current === $desired) return true;

                $other_col = $col === 'enrolled_1st' ? 'enrolled_2nd' : 'enrolled_1st';
                $notesSql = $notes !== null ? ', notes = ?' : '';
                if ($desired === 1) {
                    $sql = "UPDATE scholar_enrollments SET $col = ?, $other_col = 0, updated_at = CURRENT_TIMESTAMP" . $notesSql . ' WHERE id = ?';
                    $params = [$desired];
                    if ($notes !== null) $params[] = $notes;
                    $params[] = $row['id'];
                } else {
                    $sql = "UPDATE scholar_enrollments SET $col = ?, updated_at = CURRENT_TIMESTAMP" . $notesSql . ' WHERE id = ?';
                    $params = [$desired];
                    if ($notes !== null) $params[] = $notes;
                    $params[] = $row['id'];
                }
                $stmt2 = $db->prepare($sql);
                return $stmt2->execute($params);
            } else {
                $en1 = ($semester === '1st') ? ($enrolled ? 1 : 0) : 0;
                $en2 = ($semester === '2nd') ? ($enrolled ? 1 : 0) : 0;
                $stmt3 = $db->prepare('INSERT INTO scholar_enrollments (scholar_id, school_year_id, enrolled_1st, enrolled_2nd, notes, updated_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)');
                return $stmt3->execute([$scholar_id, $school_year_id, $en1, $en2, $notes]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }
}

if (!function_exists('getScholarEnrollment')) {
    function getScholarEnrollment($scholar_id, $school_year_id, $semester = null) {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM scholar_enrollments WHERE scholar_id = ? AND school_year_id = ? LIMIT 1");
        $stmt->execute([$scholar_id, $school_year_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

function setScholarStatus($scholar_id, $status) {
    $db = get_db();
    $allowed = ['enrolled','not_enrolled','graduated'];
    if (!in_array($status, $allowed)) return false;
    $stmt = $db->prepare("UPDATE scholars SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $scholar_id]);
}

function getScholarStatusLabel($status) {
    switch ($status) {
        case 'enrolled': return '<span class="badge bg-success">Enrolled</span>';
        case 'graduated': return '<span class="badge bg-secondary">Graduated</span>';
        default: return '<span class="badge bg-danger">Not Enrolled</span>';
    }
}

?>

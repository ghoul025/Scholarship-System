<?php
session_start();
require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// permission check: only main admin
$user_sql = "SELECT main_admin FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->execute([$_SESSION['user_id'] ?? null]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !$user || $user['main_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$q = trim($_GET['q'] ?? '');
$filter_type = $_GET['scholarship_type'] ?? '';
$filter_sex = $_GET['sex'] ?? '';
$filter_year = $_GET['year_level'] ?? '';

$where = [];
$params = [];
if ($q !== '') {
    $where[] = "(u.username LIKE ? OR s.first_name LIKE ? OR s.middle_name LIKE ? OR s.last_name LIKE ? OR CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) LIKE ? )";
    $like = "%{$q}%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($filter_type !== '') { $where[] = "s.scholarship_type = ?"; $params[] = $filter_type; }
if ($filter_sex !== '') { $where[] = "s.sex = ?"; $params[] = $filter_sex; }
if ($filter_year !== '') { $where[] = "s.year_level = ?"; $params[] = $filter_year; }

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT s.id, u.username, s.first_name, s.middle_name, s.last_name, s.phone, s.sex, s.units, s.tuition_fee, s.course, s.year_level, s.scholarship_type
    FROM scholars s
    JOIN users u ON s.user_id = u.id
    {$where_sql}
    ORDER BY s.last_name ASC, s.first_name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$scholars = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rows_html = '';
foreach ($scholars as $scholar) {
    $first = $scholar['first_name'] ?? '';
    $last = $scholar['last_name'] ?? '';
    $initials = strtoupper(substr(($first), 0, 1) . substr(($last), 0, 1));
    $tuition = is_numeric($scholar['tuition_fee']) ? number_format($scholar['tuition_fee'], 2) : htmlspecialchars($scholar['tuition_fee']);
    $sch_type = $scholar['scholarship_type'] ?? '';
    $stype_low = strtolower($sch_type);
    $badgeClass = 'bg-blue-50 text-blue-800';
    if (strpos($stype_low, 'full') !== false) { $badgeClass = 'bg-green-100 text-green-800'; }
    else if (strpos($stype_low, 'partial') !== false) { $badgeClass = 'bg-yellow-100 text-yellow-800'; }
    else if (strpos($stype_low, 'tes') !== false) { $badgeClass = 'bg-indigo-100 text-indigo-800'; }

    $rows_html .= '<tr class="hover:bg-blue-50 transition-all">';
    $rows_html .= '<td class="px-4 py-3 align-middle">';
    $rows_html .= '<div class="flex items-center gap-3">';
    $rows_html .= '<div class="w-9 h-9 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center font-semibold">'.htmlspecialchars($initials).'</div>';
    $rows_html .= '<div><div class="font-medium text-gray-800">'.htmlspecialchars($scholar['username']).'</div><div class="text-xs text-gray-500">ID: '.htmlspecialchars($scholar['id']).'</div></div>';
    $rows_html .= '</div></td>';
    $rows_html .= '<td class="px-4 py-3"><div class="font-semibold text-gray-800">'.htmlspecialchars(trim(($first) . ' ' . ($scholar['middle_name'] ?? '') . ' ' . ($last))).'</div><div class="text-sm text-gray-500">'.htmlspecialchars($scholar['course']).'</div></td>';
    $rows_html .= '<td class="px-4 py-3"><div class="text-sm text-gray-700">'.htmlspecialchars($scholar['phone']).'</div></td>';
    $rows_html .= '<td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">'.htmlspecialchars($scholar['sex']).'</span></td>';
    $rows_html .= '<td class="px-4 py-3"><div class="text-sm text-gray-700">'.htmlspecialchars($scholar['units']).'</div></td>';
    $rows_html .= '<td class="px-4 py-3"><div class="text-sm text-gray-800">₱ '. $tuition .'</div></td>';
    $rows_html .= '<td class="px-4 py-3"><div class="text-sm text-gray-700">'.htmlspecialchars($scholar['course']).'</div></td>';
    $rows_html .= '<td class="px-4 py-3"><div class="text-sm text-gray-700">'.htmlspecialchars($scholar['year_level']).'</div></td>';
    $rows_html .= '<td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium '. $badgeClass .'">'.htmlspecialchars($sch_type).'</span></td>';
    $rows_html .= '</tr>';
}

$count_sql = "SELECT COUNT(*) FROM scholars s JOIN users u ON s.user_id = u.id {$where_sql}";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();

$type_sql = "SELECT scholarship_type, COUNT(*) as count FROM scholars s JOIN users u ON s.user_id = u.id {$where_sql} GROUP BY scholarship_type";
$type_stmt = $conn->prepare($type_sql);
$type_stmt->execute($params);
$type_counts = [];
while ($r = $type_stmt->fetch(PDO::FETCH_ASSOC)) { $type_counts[$r['scholarship_type']] = (int)$r['count']; }

$sex_sql = "SELECT sex, COUNT(*) as count FROM scholars s JOIN users u ON s.user_id = u.id {$where_sql} GROUP BY sex";
$sex_stmt = $conn->prepare($sex_sql);
$sex_stmt->execute($params);
$sex_counts = [];
while ($r = $sex_stmt->fetch(PDO::FETCH_ASSOC)) { $sex_counts[$r['sex']] = (int)$r['count']; }

echo json_encode([
    'rows' => $rows_html,
    'total' => $total,
    'type_labels' => array_values(array_keys($type_counts)),
    'type_data' => array_values($type_counts),
    'sex_labels' => array_values(array_keys($sex_counts)),
    'sex_data' => array_values($sex_counts),
]);

?>
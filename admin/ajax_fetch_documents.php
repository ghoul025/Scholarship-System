<?php
session_start();
require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// Only main admin can access
$user_sql = "SELECT main_admin FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->execute([$_SESSION['user_id'] ?? null]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !$user || $user['main_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$allowed_status = ['approved','rejected','pending'];
$filter_status = isset($_GET['status']) && in_array($_GET['status'], $allowed_status) ? $_GET['status'] : null;

$base_sql = "SELECT d.id, CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name) AS full_name, d.type AS document_type, d.file_path, d.status, d.uploaded_at
    FROM documents  d
    JOIN scholars s ON d.scholar_id = s.id";
if ($filter_status) {
    $sql = $base_sql . " WHERE d.status = :status ORDER BY d.uploaded_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['status' => $filter_status]);
} else {
    $sql = $base_sql . " ORDER BY d.uploaded_at DESC";
    $stmt = $conn->query($sql);
}
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rows = '';
foreach ($documents as $doc) {
    $rows .= '<tr class="hover:bg-blue-50 transition-all">';
    $rows .= '<td class="px-4 py-2">'.htmlspecialchars($doc['full_name']).'</td>';
    $rows .= '<td class="px-4 py-2">'.htmlspecialchars($doc['document_type']).'</td>';
    $rows .= '<td class="px-4 py-2"><a href="'.htmlspecialchars($doc['file_path']).'" target="_blank" class="inline-flex items-center gap-1 bg-blue-500 text-white text-sm font-semibold px-3 py-1 rounded-full hover:bg-blue-600 transition-all"><i class="bi bi-eye"></i> View</a></td>';
    $rows .= '<td class="px-4 py-2">';
    if ($doc['status'] == 'approved') $rows .= '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">Approved</span>';
    elseif ($doc['status'] == 'rejected') $rows .= '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">Rejected</span>';
    else $rows .= '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">Pending</span>';
    $rows .= '</td>';
    $rows .= '<td class="px-4 py-2">'.htmlspecialchars($doc['uploaded_at']).'</td>';
    $rows .= '</tr>';
}

$total_documents = $conn->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$status_stmt = $conn->query("SELECT status, COUNT(*) as count FROM documents GROUP BY status");
$status_counts = [];
while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) { $status_counts[$row['status']] = (int)$row['count']; }

$chart_labels = array_values(array_keys($status_counts));
$chart_data = array_values($status_counts);

$status_html = '';
foreach ($status_counts as $status => $count) {
    $status_html .= '<li class="flex items-center justify-between">';
    $status_html .= '<a href="?status='.urlencode($status).'" class="text-sm text-blue-600 hover:underline">'.htmlspecialchars($status).'</a>';
    $status_html .= '<span class="font-semibold">'.(int)$count.'</span>';
    $status_html .= '</li>';
}

echo json_encode([
    'rows' => $rows,
    'total' => (int)$total_documents,
    'chart_labels' => $chart_labels,
    'chart_data' => $chart_data,
    'status_html' => $status_html,
]);

?>
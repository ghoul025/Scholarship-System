<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$url = $_GET['url'] ?? '';
// Disallow proxying barangays to prevent client-side auto-fill of barangay lists.
if (preg_match('/^(cities|municipalities)$/', $url)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://psgc.cloud/api/' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10-second timeout
    $response = curl_exec($ch);
    if ($response === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch data from API: ' . curl_error($ch)]);
    } else {
        echo $response;
    }
    curl_close($ch);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid API endpoint']);
}
?>
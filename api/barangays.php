<?php
header('Content-Type: application/json');

// API disabled: returning 403 to ensure frontend cannot auto-fill barangays/zipcodes.
http_response_code(403);
echo json_encode([
    'error' => 'This endpoint has been disabled. Please enter barangay and zip code manually.'
]);
exit;
?>
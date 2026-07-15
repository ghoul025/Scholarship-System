<?php
// Simple SMS helper using Semaphore (or other provider). Configure API key & endpoint in environment or here.
function send_sms($to, $message) {
    // Basic validation
    if (empty($to) || empty($message)) return false;

    // Load API config - replace with your real credentials
    $apiKey = getenv('SEMAPHORE_API_KEY') ?: '';
    $sender = getenv('SEMAPHORE_SENDER') ?: 'SCHOLAR';
    $endpoint = 'https://semaphore.co/api/v4/messages';

    if (empty($apiKey)) {
        // Log or fallback - for now, just return false
        error_log('send_sms: API key not configured');
        return false;
    }

    $payload = [
        'apikey' => $apiKey,
        'number' => $to,
        'message' => $message,
        'sendername' => $sender
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log('send_sms error: ' . $err);
        return false;
    }
    // Optionally decode and inspect response
    return $resp;
}
?>

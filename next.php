<?php
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Helper function to send a JSON response
function sendResponse($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Handle `submit-result` POST request

if ($method === 'POST' && strpos($requestUri, '/submit-result') !== false) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate input
    if (
        !isset($data['result']) || 
        !isset($data['licenseKey']) || 
        !isset($data['telegramChatID']) || 
        !isset($data['systemValue']) ||
        !isset($data['pages'])
    ) {
        sendResponse(400, ['error' => 'Missing required fields']);
    }

    // Prepare payload
    $payload = [
        'result' => $data['result'],
        'licenseKey' => $data['licenseKey'],
        'telegramChatID' => $data['telegramChatID'],
        'systemValue' => $data['systemValue'],
        'pages' => $data['pages'],
    ];

    // Send request to the external API
    $ch = curl_init('http://157.180.52.45:5040/api/submit-result');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        sendResponse(500, ['error' => 'cURL error: ' . $error]);
    }

    sendResponse($httpCode, json_decode($response, true));
}
else if ($method === 'POST' && strpos($requestUri, '/session-open') !== false) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (
        !isset($data['message']) || 
        !isset($data['telegramChatID'])
    ) {
        sendResponse(400, ['error' => 'Missing required fields']);
    }

    // Prepare payload
    $payload = [
        'message' => $data['message'],
        'telegramChatID' => $data['telegramChatID'],
    ];

    // Send request to the external API
    $ch = curl_init('http://157.180.52.45:5040/api/session-open');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        sendResponse(500, ['error' => 'cURL error: ' . $error]);
    }

    sendResponse($httpCode, json_decode($response, true));
}

// Handle `pollStatus` GET request
if ($method === 'GET' && preg_match('/\/status\/([a-zA-Z0-9]+)/', $requestUri, $matches)) {
    $sessionId = $matches[1];

    // Send request to the external API
    $ch = curl_init("http://157.180.52.45:5040/api/status/$sessionId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        sendResponse(500, ['error' => 'cURL error: ' . $error]);
    }

    sendResponse($httpCode, json_decode($response, true));
}
sendResponse(404, ['error' => 'Endpoint not found']);
?>


<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, 'Only POST requests are allowed.');
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/json') === false) {
    respond(415, 'Expected JSON request.');
}

$raw = file_get_contents('php://input');

if ($raw === false || strlen($raw) > 10000) {
    respond(413, 'Request is too large.');
}

$data = json_decode($raw, true);

if (!is_array($data)) {
    respond(400, 'Invalid JSON.');
}

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$message = $data['message'] ?? '';

if (!is_string($name) || !is_string($email) || !is_string($message)) {
    respond(400, 'Invalid form fields.');
}

$name = trim($name);
$email = trim($email);
$message = trim($message);

if (
    $name === '' ||
    strlen($name) > 240 ||
    strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($message) < 10 ||
    strlen($message) > 8000
) {
    respond(400, 'Please check your name, email and message.');
}

// Store messages outside the public web directory.
$dataDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
$dataFile = $dataDir . DIRECTORY_SEPARATOR . 'messages.txt';

if (!is_dir($dataDir) &&
    !mkdir($dataDir, 0750, true) &&
    !is_dir($dataDir)) {
    respond(500, 'Could not create data directory.');
}

$name = str_replace(["\r", "\n"], ' ', $name);
$email = str_replace(["\r", "\n"], ' ', $email);

$entry =
    "------------------------------\n" .
    'Date: ' . date(DATE_ATOM) . "\n" .
    'Name: ' . $name . "\n" .
    'Email: ' . $email . "\n" .
    "Message:\n" . $message . "\n\n";

if (file_put_contents($dataFile, $entry, FILE_APPEND | LOCK_EX) === false) {
    respond(500, 'Could not save message. Check folder permissions.');
}

respond(200, 'Your message was submitted successfully.');
?>

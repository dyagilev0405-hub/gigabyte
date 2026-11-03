<?php
declare(strict_types=1);

// Никаких выводов до JSON, никаких пробелов до <?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function out(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(405, ['ok' => false, 'error' => 'Method not allowed']);
}

$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);

// Если JSON не распарсился — попробуем обычный POST
if (!is_array($in)) {
    $in = $_POST;
}
if (!is_array($in) || !$in) {
    out(400, ['ok' => false, 'error' => 'Bad request']);
}

// CSRF
$csrf = (string)($in['csrf'] ?? '');
$sess = (string)($_SESSION['csrf'] ?? '');

if ($sess === '' || $csrf === '' || !hash_equals($sess, $csrf)) {
    out(403, ['ok' => false, 'error' => 'CSRF token invalid']);
}

// Honeypot
if (!empty($in['website'])) {
    out(200, ['ok' => true]);
}

// Валидация
$name    = trim((string)($in['name'] ?? ''));
$phone   = trim((string)($in['phone'] ?? ''));
$city    = in_array(($in['city'] ?? ''), ['yakutsk','vladivostok'], true) ? $in['city'] : 'yakutsk';
$device  = (string)($in['device'] ?? 'other');
$visit   = in_array(($in['visit'] ?? ''), ['office','onsite'], true) ? $in['visit'] : 'office';
$fault   = mb_substr(trim((string)($in['fault'] ?? '')), 0, 600);
$address = mb_substr(trim((string)($in['address'] ?? '')), 0, 200);
$consent = !empty($in['consent']);

$digits  = preg_replace('/\D+/', '', $phone);
$phoneOk = (strlen($digits) === 10) || (strlen($digits) === 11 && in_array($digits[0], ['7','8'], true));

if ($name === '' || mb_strlen($name) > 60) {
    out(422, ['ok' => false, 'error' => 'Укажите имя']);
}
if (!$phoneOk) {
    out(422, ['ok' => false, 'error' => 'Некорректный телефон']);
}
if (!$consent) {
    out(422, ['ok' => false, 'error' => 'Нужно согласие на обработку ПД']);
}

// Rate-limit по сессии
$now = time();
$_SESSION['last_lead'] = (int)($_SESSION['last_lead'] ?? 0);
if ($now - $_SESSION['last_lead'] < 10) {
    out(429, ['ok' => false, 'error' => 'Слишком часто. Подождите 10 секунд.']);
}
$_SESSION['last_lead'] = $now;

$lead = [
    'created_at'      => date('Y-m-d H:i:s'),
    'name'            => $name,
    'phone'           => $phone,
    'city'            => $city,
    'device'          => $device,
    'visit'           => $visit,
    'address'         => $address,
    'fault'           => $fault,
    'consent'         => 1,
    'consent_version' => 'privacy-v1 / terms-v1',
    'ip'              => $_SERVER['REMOTE_ADDR'] ?? '',
    'ua'              => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 300),
    'page'            => (string)($in['page'] ?? ''),
    'utm'             => (string)($in['utm'] ?? ''),
];

// 1) Лог в файл
$logDir = __DIR__ . '/storage';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
$logFile = $logDir . '/leads.txt';
@file_put_contents(
    $logFile,
    json_encode($lead, JSON_UNESCAPED_UNICODE) . PHP_EOL,
    FILE_APPEND | LOCK_EX
);

// 2) Запись в БД (если доступна — не критично, если нет)
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gigabyte;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $st = $pdo->prepare('INSERT INTO leads
    (created_at, name, phone, city, device, visit, address, fault, consent, consent_version, ip, ua, page, utm)
    VALUES (:created_at, :name, :phone, :city, :device, :visit, :address, :fault, :consent, :consent_version, :ip, :ua, :page, :utm)');
$st->execute($lead);
} catch (Throwable $e) {
    error_log('send.php DB error: ' . $e->getMessage() . ' | lead=' . json_encode($lead, JSON_UNESCAPED_UNICODE));
    // не глотаем молча — вернём ошибку, чтобы было видно
    out(500, ['ok' => false, 'error' => 'DB error: ' . $e->getMessage()]);
}

out(200, ['ok' => true]);
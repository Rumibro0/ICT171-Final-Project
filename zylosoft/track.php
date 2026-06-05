<?php
require_once __DIR__ . '/boot.php';
session_start();
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['ok' => false]); exit; }

// Validate secret token - must match what we embed in pages
define('TRACK_SECRET', 'zyl0s0ft_tr4ck_2026');
if (($data['token'] ?? '') !== TRACK_SECRET) {
    echo json_encode(['ok' => false]);
    exit;
}

// Filter out bots and crawlers
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$botPatterns = ['bot','crawler','spider','scraper','curl','wget','python','java','ruby','perl','php','go-http','httpclient','okhttp','axios','node','selenium','phantomjs','headless','lighthouse','pagespeed','googlebot','bingbot','yandex','baidu','duckduck','semrush','ahrefs','moz','majestic','screaming','sitebulb'];
foreach ($botPatterns as $bot) {
    if (stripos($ua, $bot) !== false) {
        echo json_encode(['ok' => false, 'reason' => 'bot']);
        exit;
    }
}

// Validate referrer - must come from our own domain
$ref = $_SERVER['HTTP_REFERER'] ?? '';
if (!empty($ref) && !str_contains($ref, 'nimbx.site') && !str_contains($ref, 'localhost')) {
    echo json_encode(['ok' => false, 'reason' => 'invalid_referrer']);
    exit;
}

// Validate and sanitize inputs
$local_ip  = filter_var($data['localIp'] ?? '', FILTER_VALIDATE_IP) ? $data['localIp'] : '';
$mac_hint  = substr(preg_replace('/[^a-zA-Z0-9:.\-_=+\/]/', '', $data['macHint'] ?? ''), 0, 100);
$screen    = substr(preg_replace('/[^0-9x]/', '', $data['screen'] ?? ''), 0, 20);
$language  = substr(preg_replace('/[^a-zA-Z0-9,;=\-_. ]/', '', $data['language'] ?? ''), 0, 60);
$page      = substr($data['page'] ?? '/', 0, 200);
$u         = $_SESSION['user'] ?? null;

// Only log if we have a valid page
if (empty($page) || strlen($page) < 1) {
    echo json_encode(['ok' => false]);
    exit;
}

log_visit('Page Visit', $u, $page, $local_ip, $mac_hint, $screen, $language);
echo json_encode(['ok' => true]);

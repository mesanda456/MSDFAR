<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

if (preg_match('/\.map$/i', $uri)) {
    header('Content-Type: application/json');
    echo '{"version":3,"sources":[],"mappings":""}';
    exit;
}

if (preg_match('/\.(ico|png|jpe?g|gif|svg|css|js|woff2?|ttf|eot)$/i', $uri)) {
    http_response_code(404);
    exit;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/index.php';

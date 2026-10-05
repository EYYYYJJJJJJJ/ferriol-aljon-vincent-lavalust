<?php
/**
 * PHP built-in server router for local API development and integration tests.
 * Run from the project root: php -S 127.0.0.1:8086 scripts/lab6_dev_router.php
 * Never use PHP's development server as the production web server.
 */
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$public = dirname(__DIR__) . '/public';
$asset = realpath($public . $path);
$publicPath = realpath($public) . DIRECTORY_SEPARATOR;
if ($asset !== false && str_starts_with($asset, $publicPath) && is_file($asset)
    && strtolower(pathinfo($asset, PATHINFO_EXTENSION)) !== 'php') {
    $types = ['css' => 'text/css', 'js' => 'text/javascript', 'png' => 'image/png',
        'jpg' => 'image/jpeg', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon'];
    $extension = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($types[$extension] ?? 'application/octet-stream'));
    readfile($asset);
    exit;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $public . '/index.php';
$_SERVER['PHP_SELF'] = '/index.php' . preg_replace('#^/index\.php#', '', $path);
chdir(dirname(__DIR__));
require $public . '/index.php';

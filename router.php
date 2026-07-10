<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = $uri === '' ? '/' : $uri;

if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    exit;
}

$requestedFile = __DIR__ . $uri;
if (is_file($requestedFile) && pathinfo($requestedFile, PATHINFO_EXTENSION) === 'php') {
    require $requestedFile;
    exit;
}

if (is_file($requestedFile)) {
    return false;
}

if (strpos($uri, '.') === false) {
    $phpFile = __DIR__ . $uri . '.php';
    if (is_file($phpFile)) {
        require $phpFile;
        exit;
    }
}

http_response_code(404);
echo '404 Not Found';

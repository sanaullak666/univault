<?php
declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = trim((string)$uri, '/');

// Clean query string or trailing slashes
if ($uri === '' || $uri === 'index') {
    $uri = 'index.php';
}

if (!str_ends_with($uri, '.php') && !str_contains($uri, '.')) {
    $uri .= '.php';
}

$rootDir = dirname(__DIR__);
$target = $rootDir . '/' . $uri;

if (file_exists($target) && is_file($target)) {
    chdir(dirname($target));
    require $target;
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:40px;text-align:center;">';
    echo '<h2>404 - Page Not Found</h2>';
    echo '<p>The requested page <code>' . htmlspecialchars($uri, ENT_QUOTES, 'UTF-8') . '</code> could not be found.</p>';
    echo '<p><a href="/">Return to Home</a></p>';
    echo '</body></html>';
}

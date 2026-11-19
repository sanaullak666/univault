<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);

if (session_status() === PHP_SESSION_NONE) session_start();
define('APP_NAME', 'UniVault');

$isVercel = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']);
$defaultBase = $isVercel ? '' : '/univault';
define('BASE_URL', getenv('BASE_URL') !== false ? getenv('BASE_URL') : $defaultBase);

define('UPLOAD_DIR', $isVercel ? '/tmp/uploads' : dirname(__DIR__) . '/uploads');
define('MAX_UPLOAD_BYTES', 20 * 1024 * 1024);

$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'univault_db';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$DB_PORT = getenv('DB_PORT') ?: '3306';

try {
    $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    if (getenv('DB_SSL_CA')) {
        $sslCaConst = defined('Pdo\Mysql::ATTR_SSL_CA') ? constant('Pdo\Mysql::ATTR_SSL_CA') : @constant('PDO::MYSQL_ATTR_SSL_CA');
        if ($sslCaConst !== null) {
            $options[$sslCaConst] = getenv('DB_SSL_CA');
        }
    }
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    if ($isVercel) {
        exit('<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><title>UniVault Setup</title><style>body{font-family:system-ui,-apple-system,sans-serif;background:#0f172a;color:#f8fafc;padding:40px 20px;text-align:center;}card{display:inline-block;max-width:560px;background:#1e293b;padding:32px;border-radius:12px;border:1px solid #334155;text-align:left;}h2{margin-top:0;color:#38bdf8;}pre{background:#0b1120;padding:16px;border-radius:8px;overflow-x:auto;color:#a5f3fc;font-size:14px;}p{line-height:1.6;color:#94a3b8;}</style></head><body><div class="card"><h2>Database Connection Required</h2><p>UniVault is running on Vercel Serverless! Please configure your Cloud MySQL credentials in the <b>Vercel Project Settings &rarr; Environment Variables</b>:</p><pre>DB_HOST = your-cloud-db-host&#10;DB_NAME = univault_db&#10;DB_USER = your-db-user&#10;DB_PASS = your-db-password&#10;DB_PORT = 3306</pre><p>After setting the variables, redeploy the project.</p></div></body></html>');
    }
    exit('Database connection failed. Import database.sql and check config/config.php.');
}

<?php
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);

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
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
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

// Database-backed Serverless Session Handler
if (!class_exists('PdoSessionHandler', false)) {
    class PdoSessionHandler implements SessionHandlerInterface {
        private PDO $pdo;
        public function __construct(PDO $pdo) { $this->pdo = $pdo; }
        public function open(string $path, string $name): bool { return true; }
        public function close(): bool { return true; }
        public function read(string $id): string|false {
            try {
                $stmt = $this->pdo->prepare("SELECT data FROM php_sessions WHERE id = ? AND access_time > ? LIMIT 1");
                $maxLifetime = (int)ini_get('session.gc_maxlifetime') ?: (86400 * 30);
                $stmt->execute([$id, time() - $maxLifetime]);
                $data = $stmt->fetchColumn();
                return $data !== false ? (string)$data : '';
            } catch (Throwable) {
                return '';
            }
        }
        public function write(string $id, string $data): bool {
            try {
                $stmt = $this->pdo->prepare("REPLACE INTO php_sessions (id, data, access_time) VALUES (?, ?, ?)");
                return $stmt->execute([$id, $data, time()]);
            } catch (Throwable) {
                return false;
            }
        }
        public function destroy(string $id): bool {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM php_sessions WHERE id = ?");
                return $stmt->execute([$id]);
            } catch (Throwable) {
                return false;
            }
        }
        public function gc(int $max_lifetime): int|false {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM php_sessions WHERE access_time < ?");
                $stmt->execute([time() - $max_lifetime]);
                return $stmt->rowCount();
            } catch (Throwable) {
                return false;
            }
        }
    }
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string)(86400 * 30));
    $handler = new PdoSessionHandler($pdo);
    session_set_save_handler($handler, true);

    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || $isVercel;

    session_set_cookie_params([
        'lifetime' => 86400 * 30,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

<?php
require_once __DIR__ . '/../config/config.php';
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $path=''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect(string $path): never {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header('Location: ' . url($path));
    exit;
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void { if (!current_user()) redirect('login.php'); }
function require_role(array $roles): void {
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) { http_response_code(403); exit('Access denied.'); }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Invalid security token. Refresh and try again.');
    }
}
function flash(string $type, string $message): void { $_SESSION['flash'] = [$type,$message]; }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) { [$t,$m]=$_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="alert '.e($t).'">'.e($m).'</div>'; }
}
function log_activity(PDO $pdo, int $userId, string $action, ?int $resourceId=null): void {
    $s=$pdo->prepare("INSERT INTO activity_logs(user_id,action,resource_id) VALUES(?,?,?)");
    $s->execute([$userId,$action,$resourceId]);
}
function is_saved(PDO $pdo, int $userId, int $resourceId): bool {
    $s=$pdo->prepare("SELECT 1 FROM bookmarks WHERE user_id=? AND resource_id=?");
    $s->execute([$userId,$resourceId]); return (bool)$s->fetchColumn();
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }

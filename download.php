<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare("SELECT * FROM resources WHERE id = ?");
$s->execute([$id]);
$r = $s->fetch();
$u = current_user();

if (!$r || ($r['status'] !== 'approved' && (!$u || ($u['id'] != $r['uploaded_by'] && $u['role'] !== 'admin')))) {
    http_response_code(404);
    exit('Document unavailable.');
}

// 1. Try local disk first
$path = UPLOAD_DIR . '/' . $r['stored_name'];
$fileData = null;
if (is_file($path)) {
    $fileData = @file_get_contents($path);
}

// 2. Fall back to database (persists across all serverless containers)
if ($fileData === null || $fileData === false || $fileData === '') {
    $fStmt = $pdo->prepare("SELECT file_data FROM resource_files WHERE resource_id = ?");
    $fStmt->execute([$id]);
    $fileData = $fStmt->fetchColumn();
}

if ($fileData === false || $fileData === null || $fileData === '') {
    http_response_code(404);
    exit('File missing.');
}

$pdo->prepare("INSERT INTO downloads(user_id, resource_id) VALUES(?, ?)")->execute([$u['id'] ?? null, $id]);

header('Content-Type: ' . $r['mime_type']);
header('Content-Length: ' . strlen((string)$fileData));
header('Content-Disposition: attachment; filename="' . rawurlencode($r['original_name']) . '"');
header('X-Content-Type-Options: nosniff');
echo $fileData;
exit;

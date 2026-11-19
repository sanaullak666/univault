<?php
require_once __DIR__.'/includes/functions.php';
if (current_user()) {
    log_activity($pdo, current_user()['id'], 'logout');
}
$_SESSION = [];
session_destroy();
redirect('index.php');

<?php require_once __DIR__.'/functions.php'; $u=current_user(); ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title ?? APP_NAME) ?> · UniVault</title>
<link rel="icon" type="image/png" href="<?= url('assets/favicon.png') ?>">
<link rel="shortcut icon" href="<?= url('assets/favicon.ico') ?>">
<link rel="apple-touch-icon" href="<?= url('assets/favicon.png') ?>">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script></head><body>
<header class="topbar"><a class="brand" href="<?= url('index.php') ?>"><img src="<?= url('assets/logo.png') ?>" alt="UniVault Logo" class="brand-logo"></a>
<nav><a href="<?= url('resources.php') ?>">Explore</a><?php if($u): ?><a href="<?= url('dashboard.php') ?>">Dashboard</a><?php if($u['role']==='admin'): ?><a href="<?= url('admin.php') ?>">Admin</a><?php endif; ?><a href="<?= url('upload.php') ?>">Contribute</a><a href="<?= url('logout.php') ?>">Logout</a><?php else: ?><a href="<?= url('login.php') ?>">Login</a><a class="nav-cta" href="<?= url('register.php') ?>">Join UniVault</a><?php endif; ?></nav></header>
<main class="container"><?php show_flash(); ?>

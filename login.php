<?php
require_once __DIR__.'/includes/functions.php';
if(current_user()) redirect('dashboard.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$email=strtolower(trim($_POST['email']??''));$pass=$_POST['password']??'';$s=$pdo->prepare("SELECT * FROM users WHERE email=? AND status='active'");$s->execute([$email]);$u=$s->fetch();if($u&&password_verify($pass,$u['password_hash'])){session_regenerate_id(true);$_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role'],'department_id'=>$u['department_id']];log_activity($pdo,(int)$u['id'],'login');redirect('dashboard.php');}else $error='Email or password is incorrect.';}
$page_title='Sign in';require __DIR__.'/includes/header.php';?>
<div class="auth-wrap"><form class="form-card" method="post"><span class="eyebrow">WELCOME BACK</span><h1>Sign in to UniVault</h1><p>Access your academic resource workspace.</p><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><?= csrf_field() ?><label>Email address<input type="email" name="email" required></label><label>Password<input type="password" name="password" required></label><button class="btn primary full">Sign in</button><p class="form-foot">New to UniVault? <a href="<?= url('register.php') ?>">Create an account</a></p></form></div>
<?php require __DIR__.'/includes/footer.php'; ?>
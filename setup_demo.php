<?php
require_once __DIR__.'/includes/functions.php';
$accounts=[
 ['UniVault Administrator','admin@univault.local','password','admin']
];
foreach($accounts as [$name,$email,$pass,$role]){
 $s=$pdo->prepare("SELECT id FROM users WHERE email=?");$s->execute([$email]);
 if(!$s->fetch()){$ins=$pdo->prepare("INSERT INTO users(name,email,password_hash,role,status) VALUES(?,?,?,?, 'active')");$ins->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT),$role]);}
}
?><!doctype html><html><body style="font-family:Arial;padding:40px"><h1>UniVault admin account configured</h1><p>Admin: admin@univault.local / password</p><p><b>Security:</b> Delete setup_demo.php now.</p><a href="login.php">Go to login</a></body></html>

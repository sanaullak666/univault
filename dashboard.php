<?php
require_once __DIR__.'/includes/functions.php';require_login();$u=current_user();$page_title='My dashboard';
$s=$pdo->prepare("SELECT COUNT(*) FROM resources WHERE uploaded_by=?");$s->execute([$u['id']]);$mine=(int)$s->fetchColumn();
$s=$pdo->prepare("SELECT COUNT(*) FROM resources WHERE uploaded_by=? AND status='pending'");$s->execute([$u['id']]);$pending=(int)$s->fetchColumn();
$s=$pdo->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id=?");$s->execute([$u['id']]);$saved=(int)$s->fetchColumn();
$s=$pdo->prepare("SELECT r.* FROM resources r WHERE r.uploaded_by=? ORDER BY r.created_at DESC LIMIT 8");$s->execute([$u['id']]);$uploads=$s->fetchAll();
require __DIR__.'/includes/header.php';?>
<section class="page-heading"><span class="eyebrow">YOUR WORKSPACE</span><h1>Hello, <?= e($u['name']) ?> 👋</h1><p>Manage your contributions and keep track of useful academic resources.</p></section>
<div class="stats-grid"><div class="stat-card"><span>My uploads</span><b><?= $mine ?></b></div><div class="stat-card"><span>Awaiting approval</span><b><?= $pending ?></b></div><div class="stat-card"><span>Bookmarks</span><b><?= $saved ?></b></div><div class="stat-card"><span>Account type</span><b class="role-value"><?= e(ucfirst($u['role'])) ?></b></div></div>
<div class="section-head"><div><h2>My contributions</h2><p>Review the status of resources you've shared.</p></div><a class="btn primary" href="<?= url('upload.php') ?>">＋ Upload resource</a></div>
<div class="table-wrap"><table><thead><tr><th>Resource</th><th>Type</th><th>Status</th><th>Uploaded</th></tr></thead><tbody><?php foreach($uploads as $r): ?><tr><td><a href="<?= url('resource.php?id='.$r['id']) ?>"><?= e($r['title']) ?></a></td><td><?= e(str_replace('_',' ',$r['type'])) ?></td><td><span class="status <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td><td><?= e(date('d M Y',strtotime($r['created_at']))) ?></td></tr><?php endforeach; if(!$uploads): ?><tr><td colspan="4">You haven't uploaded any resources yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require __DIR__.'/includes/footer.php';?>
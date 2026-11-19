<?php
require_once __DIR__.'/includes/functions.php';
$page_title='University Academic Resource Hub';
$stats=$pdo->query("SELECT COUNT(*) FROM resources WHERE status='approved'")->fetchColumn();
$departments=$pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$recent=$pdo->query("SELECT r.*,u.name uploader,s.name subject_name,s.code subject_code FROM resources r JOIN users u ON u.id=r.uploaded_by LEFT JOIN subjects s ON s.id=r.subject_id WHERE r.status='approved' ORDER BY r.created_at DESC LIMIT 6")->fetchAll();
require __DIR__.'/includes/header.php'; ?>
<section class="hero">
  <div class="hero-watermark" aria-hidden="true"></div>
  <div class="hero-copy">
    <span class="eyebrow">PONDICHERRY UNIVERSITY · KNOWLEDGE COMMUNITY</span>
    <h1>Every resource.<br><em>One university.</em></h1>
    <p>Discover previous question papers, syllabus documents, notes and learning materials shared by students and faculty.</p>
    <div class="hero-actions">
      <a class="btn primary" href="<?= url('resources.php') ?>">Explore resources <span>→</span></a>
      <a class="btn light" href="<?= url('register.php') ?>">Contribute materials</a>
    </div>
    <div class="hero-stats">
      <div><b><?= (int)$stats ?></b><span>Approved resources</span></div>
      <div><b><?= (int)$departments ?></b><span>Departments listed</span></div>
      <div><b>Open</b><span>Community contributions</span></div>
    </div>
  </div>
  <div class="hero-art">
    <div class="hero-glow" aria-hidden="true"></div>
    <div class="hero-chip chip-1">
      <span>▤</span>
      <div>
        <b>Question Papers</b>
        <small>Previous semester exams</small>
      </div>
    </div>
    <div class="hero-chip chip-2">
      <span>▧</span>
      <div>
        <b>Syllabus & Units</b>
        <small>CSCA 512 & MCA modules</small>
      </div>
    </div>
    <div class="hero-chip chip-3">
      <span>✎</span>
      <div>
        <b>Lecture Notes</b>
        <small>Verified study materials</small>
      </div>
    </div>
  </div>
</section>
<div class="page-watermark" aria-hidden="true"></div>
<section class="section"><div class="section-head"><div><span class="eyebrow">BROWSE LIBRARY</span><h2>Find what you need</h2><p>Explore resources by academic category.</p></div><a class="text-link" href="<?= url('resources.php') ?>">View all resources →</a></div><div class="category-grid">
<a class="category-card" href="<?= url('resources.php?type=question_paper') ?>"><span class="cat-icon violet">▤</span><h3>Question papers</h3><p>Previous exams, internals and semester papers.</p></a>
<a class="category-card" href="<?= url('resources.php?type=syllabus') ?>"><span class="cat-icon blue">▧</span><h3>Syllabus & modules</h3><p>Course outlines, units and topic breakdowns.</p></a>
<a class="category-card" href="<?= url('resources.php?type=notes') ?>"><span class="cat-icon orange">✎</span><h3>Notes & materials</h3><p>Student notes, presentations and references.</p></a>
<a class="category-card" href="<?= url('resources.php?type=assignment') ?>"><span class="cat-icon green">▣</span><h3>Assignments</h3><p>Academic references and practical materials.</p></a></div></section>
<section class="section"><div class="section-head"><div><span class="eyebrow">LATEST CONTRIBUTIONS</span><h2>Recently shared</h2></div><a class="text-link" href="<?= url('resources.php') ?>">Browse library →</a></div><div class="resource-grid"><?php foreach($recent as $r): ?><article class="resource-card"><div class="file-thumb"><?= strtoupper(e(pathinfo($r['original_name'],PATHINFO_EXTENSION) ?: 'FILE')) ?></div><div class="resource-info"><span class="pill"><?= e(str_replace('_',' ',$r['type'])) ?></span><h3><?= e($r['title']) ?></h3><p><?= e($r['subject_code'] ? $r['subject_code'].' · '.$r['subject_name'] : 'University resource') ?></p><small>Shared by <?= e($r['uploader']) ?></small><a class="card-link" href="<?= url('resource.php?id='.$r['id']) ?>">View resource →</a></div></article><?php endforeach; if(!$recent): ?><div class="empty">No approved resources yet. Be the first to contribute.</div><?php endif; ?></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
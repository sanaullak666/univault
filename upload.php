<?php
require_once __DIR__.'/includes/functions.php';
require_login();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? '';
    $dept = (int)($_POST['department_id'] ?? 0);
    $courseName = trim($_POST['course'] ?? '');
    $semester = (int)($_POST['semester_id'] ?? 0);
    $subjectName = trim($_POST['subject'] ?? '');
    $year = (int)($_POST['exam_year'] ?? 0);

    $allowed = ['question_paper', 'syllabus', 'notes', 'assignment', 'presentation', 'other'];
    if ($title === '' || !in_array($type, $allowed, true) || empty($_FILES['document']['name'])) {
        $errors[] = 'Title, resource category and document are required.';
    } elseif ($_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed.';
    } elseif ($_FILES['document']['size'] > MAX_UPLOAD_BYTES) {
        $errors[] = 'Maximum file size is 20 MB.';
    } else {
        $tmp = $_FILES['document']['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        $valid = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];

        if (!isset($valid[$ext]) || $valid[$ext] !== $mime) {
            $errors[] = 'Allowed formats: PDF, DOC, DOCX, PPT and PPTX.';
        } else {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            $stored = bin2hex(random_bytes(18)) . '.' . $ext;
            $dest = UPLOAD_DIR . '/' . $stored;

            if (move_uploaded_file($tmp, $dest)) {
                try {
                    // Resolve or create Course
                    $courseId = null;
                    if ($courseName !== '') {
                        $cStmt = $pdo->prepare("SELECT id FROM courses WHERE name = ? LIMIT 1");
                        $cStmt->execute([$courseName]);
                        $courseId = $cStmt->fetchColumn();
                        if (!$courseId) {
                            $cIns = $pdo->prepare("INSERT INTO courses (department_id, name, level) VALUES (?, ?, 'Postgraduate')");
                            $cIns->execute([$dept ?: 1, $courseName]);
                            $courseId = (int)$pdo->lastInsertId();
                        } else {
                            $courseId = (int)$courseId;
                        }
                    }

                    // Resolve or create Subject
                    $subjectId = null;
                    if ($subjectName !== '') {
                        $sStmt = $pdo->prepare("SELECT id FROM subjects WHERE name = ? OR code = ? LIMIT 1");
                        $sStmt->execute([$subjectName, $subjectName]);
                        $subjectId = $sStmt->fetchColumn();
                        if (!$subjectId) {
                            $words = preg_split('/\s+/', $subjectName);
                            $code = '';
                            if (count($words) >= 2) {
                                foreach ($words as $w) {
                                    if (preg_match('/[A-Za-z0-9]/', $w)) {
                                        $code .= strtoupper($w[0]);
                                    }
                                }
                            }
                            if (strlen($code) < 3) {
                                $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $subjectName), 0, 7)) ?: 'SUB';
                            }
                            $sIns = $pdo->prepare("INSERT INTO subjects (course_id, semester_id, code, name) VALUES (?, ?, ?, ?)");
                            $sIns->execute([$courseId ?: 1, $semester ?: 1, $code, $subjectName]);
                            $subjectId = (int)$pdo->lastInsertId();
                        } else {
                            $subjectId = (int)$subjectId;
                        }
                    }

                    $s = $pdo->prepare("INSERT INTO resources(title,description,type,department_id,course_id,semester_id,subject_id,module_id,exam_year,exam_type,original_name,stored_name,mime_type,file_size,uploaded_by,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')");
                    $s->execute([
                        $title,
                        $desc,
                        $type,
                        $dept ?: null,
                        $courseId ?: null,
                        $semester ?: null,
                        $subjectId ?: null,
                        null,
                        $year ?: null,
                        null,
                        basename($_FILES['document']['name']),
                        $stored,
                        $mime,
                        (int)$_FILES['document']['size'],
                        current_user()['id']
                    ]);
                    $id = (int)$pdo->lastInsertId();
                    log_activity($pdo, current_user()['id'], 'upload', $id);
                    flash('success', 'Upload received. It will appear in the public library after administrator approval.');
                    redirect('dashboard.php');
                } catch (Throwable $e) {
                    @unlink($dest);
                    $errors[] = 'Could not save resource details. ' . $e->getMessage();
                }
            } else {
                $errors[] = 'Could not store uploaded file.';
            }
        }
    }
}

$departments = $pdo->query("SELECT id,name FROM departments ORDER BY name")->fetchAll();
$semesters   = $pdo->query("SELECT id,name FROM semesters ORDER BY id")->fetchAll();
$page_title  = 'Contribute a resource';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
  <span class="eyebrow">COMMUNITY CONTRIBUTION</span>
  <h1>Share a resource</h1>
  <p>Help other students learn. Every upload is reviewed before publication.</p>
</section>

<form class="form-card wide" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <?php foreach ($errors as $x): ?>
    <div class="alert error"><?= e($x) ?></div>
  <?php endforeach; ?>

  <div class="form-grid">
    <label>Resource title *
      <input name="title" required maxlength="200" placeholder="e.g. CSCA 512 End Semester Question Paper">
    </label>

    <label>Resource category *
      <select name="type" required>
        <option value="">Choose category</option>
        <option value="question_paper">Previous question paper</option>
        <option value="syllabus">Syllabus</option>
        <option value="notes">Notes / study material</option>
        <option value="assignment">Assignment</option>
        <option value="presentation">Presentation</option>
        <option value="other">Other</option>
      </select>
    </label>

    <label>Department
      <select name="department_id" id="department">
        <option value="">Select department</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Course
      <input name="course" id="course" placeholder="e.g. Master of Computer Applications (MCA)">
    </label>

    <label>Semester
      <select name="semester_id" id="semester">
        <option value="">Select semester</option>
        <?php foreach ($semesters as $s): ?>
          <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Subject
      <input name="subject" id="subject" placeholder="e.g. Internet and Web Technologies">
    </label>

    <label>Examination year (question papers)
      <input type="number" name="exam_year" min="2000" max="<?= date('Y') + 1 ?>" placeholder="<?= date('Y') ?>">
    </label>
  </div>

  <label>Description
    <textarea name="description" rows="4" placeholder="Briefly describe this resource..."></textarea>
  </label>

  <label class="file-drop">Choose document (PDF, DOC, DOCX, PPT, PPTX · max 20 MB)
    <input type="file" name="document" accept=".pdf,.doc,.docx,.ppt,.pptx" required>
  </label>

  <button class="btn primary">Submit for approval</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>

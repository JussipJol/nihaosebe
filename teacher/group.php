<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/lang.php';
require_once dirname(__DIR__) . '/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /teacher/group.php?id='.((int)$_GET['id'])); exit; }
$user = requireAuth('teacher');
$groupId = (int)($_GET['id'] ?? 0);
if (!$groupId) { header('Location: /teacher/'); exit; }

$st = db()->prepare('SELECT * FROM `groups` WHERE id=? AND teacher_id=?');
$st->execute([$groupId, $user['id']]);
$group = $st->fetch();
if (!$group) { header('Location: /teacher/'); exit; }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_lesson'])) {
        $title = trim($_POST['lesson_title'] ?? '');
        $date  = $_POST['lesson_date'] ?? '';
        if (!$title || !$date) { $error = t('err_required'); }
        else { db()->prepare('INSERT INTO lessons (group_id,title,lesson_date) VALUES (?,?,?)')->execute([$groupId,$title,$date]); $success = t('saved_ok'); }
    } elseif (isset($_POST['delete_group'])) {
        db()->prepare('DELETE FROM `groups` WHERE id=? AND teacher_id=?')->execute([$groupId,$user['id']]);
        header('Location: /teacher/'); exit;
    }
}

$st = db()->prepare("
    SELECT l.*, COUNT(DISTINCT sg.student_id) AS ts,
           SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS ps
    FROM lessons l JOIN `groups` g ON g.id=l.group_id
    LEFT JOIN student_groups sg ON sg.group_id=l.group_id
    LEFT JOIN attendance a ON a.lesson_id=l.id AND a.student_id=sg.student_id
    WHERE l.group_id=? GROUP BY l.id ORDER BY l.lesson_date DESC
");
$st->execute([$groupId]);
$lessons = $st->fetchAll();

$st = db()->prepare("SELECT u.id,u.name,u.email FROM student_groups sg JOIN users u ON u.id=sg.student_id WHERE sg.group_id=? ORDER BY u.name");
$st->execute([$groupId]);
$students = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($group['name']) ?> — <?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/teacher/', 'id='.$groupId) ?>
<div class="main">
  <div style="max-width:960px">

    <a href="/teacher/" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:#9A7A2A;text-decoration:none;margin-bottom:20px">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      <?= h(t('back_to_dashboard')) ?>
    </a>

    <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:28px">
      <div>
        <h1 class="page-title"><?= h($group['name']) ?></h1>
        <?php if ($group['subject']): ?><p style="color:#C9A84C;font-weight:700;font-size:15px;margin:3px 0"><?= h($group['subject']) ?></p><?php endif; ?>
      </div>
      <div style="background:#FAFAF8;border:1px solid #EDE5D4;border-radius:12px;padding:10px 16px;display:flex;align-items:center;gap:12px">
        <span style="font-size:12px;color:#7B6F5E;font-weight:600"><?= h(t('invite_code')) ?>:</span>
        <span style="font-family:monospace;font-weight:900;font-size:20px;letter-spacing:.2em;color:#C9A84C"><?= h($group['invite_code']) ?></span>
      </div>
    </div>

    <?php if ($error): ?><div class="alert-error" style="margin-bottom:16px"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-success" style="margin-bottom:16px"><?= h($success) ?></div><?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-6">
      <div style="grid-column:span 2">
        <div class="card card-p" style="margin-bottom:16px">
          <h2 style="font-size:15px;font-weight:800;color:#1A1510;margin:0 0 14px"><?= h(t('add_lesson')) ?></h2>
          <form method="POST">
            <div class="grid sm:grid-cols-2 gap-3" style="margin-bottom:12px">
              <div>
                <label class="label"><?= h(t('lesson_title')) ?></label>
                <input type="text" name="lesson_title" required class="input" placeholder="<?= h(t('lesson_title')) ?>">
              </div>
              <div>
                <label class="label"><?= h(t('lesson_date')) ?></label>
                <input type="date" name="lesson_date" required class="input" value="<?= date('Y-m-d') ?>">
              </div>
            </div>
            <button type="submit" name="add_lesson" class="btn btn-dark">+ <?= h(t('add_lesson')) ?></button>
          </form>
        </div>

        <?php if (empty($lessons)): ?>
          <div class="card card-p" style="text-align:center;color:#7B6F5E"><?= h(t('no_lessons')) ?></div>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:8px">
            <?php foreach ($lessons as $l):
              $pct = (int)$l['ts'] > 0 ? round((int)$l['ps']/(int)$l['ts']*100) : 0;
              $c = $pct>=80?'#2D7A4F':($pct>=60?'#9A6800':'#C0392B');
            ?>
            <a href="/teacher/lesson.php?id=<?= $l['id'] ?>" style="text-decoration:none">
              <div class="card card-hover" style="padding:14px 18px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;transition:all .15s">
                <div>
                  <p style="font-size:15px;font-weight:700;color:#1A1510;margin:0 0 2px"><?= h($l['title']) ?></p>
                  <p style="font-size:12px;color:#7B6F5E;margin:0"><?= date('d.m.Y',strtotime($l['lesson_date'])) ?></p>
                </div>
                <div style="display:flex;align-items:center;gap:12px">
                  <div style="text-align:right">
                    <span style="font-size:16px;font-weight:900;color:<?= $c ?>"><?= $pct ?>%</span>
                    <p style="font-size:11px;color:#7B6F5E;margin:0"><?= h(t('attended')) ?></p>
                  </div>
                  <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#C0C0B0" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div style="display:flex;flex-direction:column;gap:12px">
        <div class="card card-p">
          <h2 style="font-size:15px;font-weight:800;color:#1A1510;margin:0 0 14px">
            <?= h(t('students_list')) ?> <span style="color:#7B6F5E;font-weight:400">(<?= count($students) ?>)</span>
          </h2>
          <?php if (empty($students)): ?>
            <p style="font-size:13px;color:#7B6F5E"><?= h(t('no_students')) ?></p>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px">
              <?php foreach ($students as $s): ?>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:32px;height:32px;background:#1A1510;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:12px;color:#C9A84C;flex-shrink:0">
                  <?= mb_strtoupper(mb_substr($s['name'],0,1)) ?>
                </div>
                <div style="min-width:0">
                  <p style="font-size:13px;font-weight:700;color:#1A1510;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h($s['name']) ?></p>
                  <p style="font-size:11px;color:#7B6F5E;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h($s['email']) ?></p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="card card-p">
          <form method="POST" onsubmit="return confirm('<?= h(t('confirm_delete')) ?>')">
            <button type="submit" name="delete_group" class="btn btn-danger" style="width:100%"><?= h(t('delete_group')) ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>

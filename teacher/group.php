<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /teacher/group.php?id='.((int)$_GET['id'])); exit; }
$user = requireAuth('teacher');
$gid = (int)($_GET['id']??0);
if (!$gid) { header('Location: /teacher/'); exit; }

$st = db()->prepare('SELECT * FROM `groups` WHERE id=? AND teacher_id=?');
$st->execute([$gid,$user['id']]);
$group = $st->fetch();
if (!$group) { header('Location: /teacher/'); exit; }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_lesson'])) {
        $t=trim($_POST['lesson_title']??''); $d=$_POST['lesson_date']??'';
        if (!$t||!$d) { $error=t('err_required'); }
        else { db()->prepare('INSERT INTO lessons (group_id,title,lesson_date) VALUES (?,?,?)')->execute([$gid,$t,$d]); $success=t('saved_ok'); }
    } elseif (isset($_POST['delete_group'])) {
        db()->prepare('DELETE FROM `groups` WHERE id=? AND teacher_id=?')->execute([$gid,$user['id']]);
        header('Location: /teacher/'); exit;
    }
}

$st = db()->prepare("
    SELECT l.*,COUNT(DISTINCT sg.student_id) AS ts,SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS ps
    FROM lessons l JOIN `groups` g ON g.id=l.group_id
    LEFT JOIN student_groups sg ON sg.group_id=l.group_id
    LEFT JOIN attendance a ON a.lesson_id=l.id AND a.student_id=sg.student_id
    WHERE l.group_id=? GROUP BY l.id ORDER BY l.lesson_date DESC
");
$st->execute([$gid]);
$lessons = $st->fetchAll();

$st = db()->prepare("SELECT u.id,u.name,u.login FROM student_groups sg JOIN users u ON u.id=sg.student_id WHERE sg.group_id=? ORDER BY u.name");
$st->execute([$gid]);
$students = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($group['name']) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/teacher/', 'id='.$gid) ?>
<div class="main">
<div class="content">

  <a href="/teacher/" class="back-link">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    <?= h(t('back_to_dashboard')) ?>
  </a>

  <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:24px">
    <div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
        <h1 class="page-title" style="margin-bottom:0"><?= h($group['name']) ?></h1>
        <?php if ($group['hsk_level']): ?>
          <span style="background:var(--black);color:#fff;border-radius:7px;padding:4px 10px;font-size:13px;font-weight:700">
            HSK <?= (int)$group['hsk_level'] ?>
          </span>
        <?php endif; ?>
      </div>
      <?php if ($group['schedule'] || $group['lesson_time']): ?>
      <div style="display:flex;flex-wrap:wrap;align-items:center;gap:5px">
        <?php if ($group['schedule']): ?>
          <?php foreach (explode(',', $group['schedule']) as $d): ?>
            <span style="background:var(--bg-sub);border:1px solid var(--border);border-radius:5px;padding:2px 7px;font-size:11px;font-weight:600;color:var(--muted)"><?= h($d) ?></span>
          <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($group['lesson_time']): ?>
          <span style="font-size:13px;font-weight:600;color:var(--muted)"><?= h($group['lesson_time']) ?></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <div style="background:var(--bg-sub);border:1px solid var(--border);border-radius:8px;padding:8px 14px;display:flex;align-items:center;gap:10px;flex-shrink:0">
      <span style="font-size:11px;color:var(--muted);font-weight:500"><?= h(t('invite_code')) ?></span>
      <span class="mono" style="font-size:18px;font-weight:800;color:var(--black);letter-spacing:.15em"><?= h($group['invite_code']) ?></span>
    </div>
  </div>

  <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:14px"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:14px">✓ <?= h($success) ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr minmax(0,260px);gap:16px;align-items:start">

    <div>
      <!-- Add lesson -->
      <div class="card p5" style="margin-bottom:12px">
        <div style="font-size:14px;font-weight:700;color:var(--black);margin-bottom:12px"><?= h(t('add_lesson')) ?></div>
        <form method="POST" style="display:grid;grid-template-columns:1fr auto auto;gap:8px;align-items:end">
          <div>
            <label class="label"><?= h(t('lesson_title')) ?></label>
            <input type="text" name="lesson_title" required class="input" placeholder="<?= h(t('lesson_title')) ?>">
          </div>
          <div>
            <label class="label"><?= h(t('lesson_date')) ?></label>
            <input type="date" name="lesson_date" required class="input" value="<?= date('Y-m-d') ?>">
          </div>
          <button type="submit" name="add_lesson" class="btn btn-black" style="margin-bottom:0">+</button>
        </form>
      </div>

      <!-- Lessons -->
      <?php if (empty($lessons)): ?>
        <div class="card p5" style="text-align:center;color:var(--muted);font-size:14px"><?= h(t('no_lessons')) ?></div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:6px">
          <?php foreach ($lessons as $l):
            $pct = (int)$l['ts']>0 ? round((int)$l['ps']/(int)$l['ts']*100) : 0;
            $pc  = $pct>=80?'var(--green)':($pct>=60?'var(--amber)':'var(--red)');
          ?>
          <a href="/teacher/lesson.php?id=<?= $l['id'] ?>" class="card card-link" style="padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
              <div style="font-size:14px;font-weight:600;color:var(--black)"><?= h($l['title']) ?></div>
              <div style="font-size:12px;color:var(--muted);margin-top:1px"><?= date('d.m.Y',strtotime($l['lesson_date'])) ?></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
              <span style="font-size:14px;font-weight:700;color:<?= $pc ?>"><?= $pct ?>%</span>
              <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#D4D4D8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Sidebar: students + delete -->
    <div style="display:flex;flex-direction:column;gap:10px">
      <div class="card p5">
        <div style="font-size:13px;font-weight:600;color:var(--black);margin-bottom:12px">
          <?= h(t('students_list')) ?> <span style="color:var(--muted);font-weight:400">(<?= count($students) ?>)</span>
        </div>
        <?php if (empty($students)): ?>
          <p style="font-size:13px;color:var(--muted)"><?= h(t('no_students')) ?></p>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:8px">
            <?php foreach ($students as $s): ?>
            <div style="display:flex;align-items:center;gap:8px">
              <div class="avatar-sm"><?= mb_strtoupper(mb_substr($s['name'],0,1)) ?></div>
              <div style="min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--black);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($s['name']) ?></div>
                <div style="font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:monospace"><?= h($s['login']) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="card p4">
        <form method="POST" onsubmit="return confirm('<?= h(t('confirm_delete')) ?>')">
          <button type="submit" name="delete_group" class="btn btn-danger" style="width:100%;justify-content:center">
            <?= h(t('delete_group')) ?>
          </button>
        </form>
      </div>
    </div>
  </div>

</div>
</div>
</body>
</html>

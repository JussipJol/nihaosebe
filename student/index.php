<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /student/'); exit; }
$user = requireAuth('student');
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['join_code'] ?? ''));
    if (!$code) { $error = t('err_required'); }
    else {
        $st = db()->prepare('SELECT id FROM `groups` WHERE invite_code=?');
        $st->execute([$code]);
        $g = $st->fetch();
        if (!$g) { $error = t('err_code_invalid'); }
        else {
            try {
                db()->prepare('INSERT INTO student_groups (student_id,group_id) VALUES (?,?)')->execute([$user['id'],$g['id']]);
                $success = t('saved_ok');
            } catch (PDOException $e) { $error = t('err_already_member'); }
        }
    }
}

$st = db()->prepare("
    SELECT g.id,g.name,g.subject,u.name AS tn,
           COUNT(DISTINCT l.id) AS tl,
           SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS att,
           SUM(CASE WHEN a.status='absent'  THEN 1 ELSE 0 END) AS msd
    FROM student_groups sg
    JOIN `groups` g ON g.id=sg.group_id
    JOIN users u ON u.id=g.teacher_id
    LEFT JOIN lessons l ON l.group_id=g.id
    LEFT JOIN attendance a ON a.lesson_id=l.id AND a.student_id=?
    WHERE sg.student_id=?
    GROUP BY g.id ORDER BY g.created_at DESC
");
$st->execute([$user['id'],$user['id']]);
$groups = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('student_dashboard')) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/student/') ?>
<div class="main">
<div class="content">

  <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin-bottom:28px">
    <div>
      <h1 class="page-title"><?= h(t('student_dashboard')) ?></h1>
      <p class="page-sub"><?= h($user['name']) ?></p>
    </div>
    <form method="POST" style="display:flex;gap:8px">
      <input type="text" name="join_code" maxlength="6" class="input mono"
             placeholder="<?= h(t('join_code_placeholder')) ?>"
             style="width:150px;text-transform:uppercase;letter-spacing:.12em;font-weight:700">
      <button type="submit" class="btn btn-black"><?= h(t('btn_join')) ?></button>
    </form>
  </div>

  <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:16px"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:16px">✓ <?= h($success) ?></div><?php endif; ?>

  <?php if (empty($groups)): ?>
    <div class="card p6" style="text-align:center;padding:56px 24px">
      <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#D4D4D8" stroke-width="1.5" style="margin:0 auto 14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      <p style="color:var(--muted);font-size:14px"><?= h(t('no_groups')) ?></p>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px">
      <?php foreach ($groups as $g):
        $tl  = (int)$g['tl'];
        $att = (int)$g['att'];
        $pct = $tl > 0 ? round($att/$tl*100) : 0;
        $pc  = $pct >= 80 ? 'var(--green)' : ($pct >= 60 ? 'var(--amber)' : 'var(--red)');
        $bc  = $pct >= 80 ? 'progress-green' : ($pct >= 60 ? 'progress-amber' : 'progress-red');
        $sc  = $pct >= 80 ? 'badge-green' : ($pct >= 60 ? 'badge-amber' : 'badge-red');
      ?>
      <a href="/student/group.php?id=<?= $g['id'] ?>" class="card card-link p5">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
          <div style="min-width:0">
            <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($g['name']) ?></div>
            <?php if ($g['subject']): ?><div style="font-size:12px;color:var(--red);font-weight:600;margin-bottom:2px"><?= h($g['subject']) ?></div><?php endif; ?>
            <div style="font-size:12px;color:var(--muted)"><?= h(t('teacher')) ?>: <?= h($g['tn']) ?></div>
          </div>
          <span class="badge <?= $sc ?>" style="flex-shrink:0;margin-left:8px"><?= $pct ?>%</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px">
          <div class="stat" style="background:var(--bg-sub)"><div class="stat-value"><?= $tl ?></div><div class="stat-label"><?= h(t('total_lessons')) ?></div></div>
          <div class="stat" style="background:var(--green-bg)"><div class="stat-value" style="color:var(--green)"><?= $att ?></div><div class="stat-label" style="color:var(--green)"><?= h(t('attended')) ?></div></div>
          <div class="stat" style="background:var(--red-bg)"><div class="stat-value" style="color:var(--red)"><?= (int)$g['msd'] ?></div><div class="stat-label" style="color:var(--red)"><?= h(t('missed')) ?></div></div>
        </div>
        <div class="progress"><div class="progress-fill <?= $bc ?>" style="width:<?= $pct ?>%"></div></div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
</div>
</body>
</html>

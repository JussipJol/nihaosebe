<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /teacher/'); exit; }
$user = requireAuth('teacher');
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $name = trim($_POST['group_name']??'');
    $subj = trim($_POST['subject']??'');
    if (!$name) { $error = t('err_required'); }
    else {
        $code = generateInviteCode();
        db()->prepare('INSERT INTO `groups` (name,subject,teacher_id,invite_code) VALUES (?,?,?,?)')->execute([$name,$subj?:null,$user['id'],$code]);
        $success = t('saved_ok');
    }
}

$st = db()->prepare("
    SELECT g.*,COUNT(DISTINCT sg.student_id) AS sc,COUNT(DISTINCT l.id) AS lc
    FROM `groups` g
    LEFT JOIN student_groups sg ON sg.group_id=g.id
    LEFT JOIN lessons l ON l.group_id=g.id
    WHERE g.teacher_id=?
    GROUP BY g.id ORDER BY g.created_at DESC
");
$st->execute([$user['id']]);
$groups = $st->fetchAll();
$showForm = $error || $success;
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('teacher_dashboard')) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/teacher/') ?>
<div class="main">
<div class="content">

  <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;margin-bottom:28px">
    <div>
      <h1 class="page-title"><?= h(t('teacher_dashboard')) ?></h1>
      <p class="page-sub"><?= h($user['name']) ?></p>
    </div>
    <button onclick="var f=document.getElementById('cf');f.style.display=f.style.display===''?'none':''" class="btn btn-black">
      <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      <?= h(t('create_group')) ?>
    </button>
  </div>

  <!-- Create form -->
  <div id="cf" style="display:<?= $showForm?'':'none' ?>;margin-bottom:20px">
    <div class="card p5">
      <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:14px"><?= h(t('create_group')) ?></div>
      <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:12px"><?= h($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:12px">✓ <?= h($success) ?></div><?php endif; ?>
      <form method="POST">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
          <div>
            <label class="label"><?= h(t('group_name')) ?> *</label>
            <input type="text" name="group_name" required class="input" placeholder="Английский A1">
          </div>
          <div>
            <label class="label"><?= h(t('subject')) ?></label>
            <input type="text" name="subject" class="input" placeholder="Английский язык">
          </div>
        </div>
        <div style="display:flex;gap:8px">
          <button type="submit" name="create_group" class="btn btn-black"><?= h(t('btn_create')) ?></button>
          <button type="button" onclick="document.getElementById('cf').style.display='none'" class="btn btn-ghost"><?= getLang()==='ru'?'Отмена':(getLang()==='kz'?'Бас тарту':'Cancel') ?></button>
        </div>
      </form>
    </div>
  </div>

  <!-- Groups -->
  <?php if (empty($groups)): ?>
    <div class="card p6" style="text-align:center;padding:56px 24px">
      <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#D4D4D8" stroke-width="1.5" style="margin:0 auto 14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
      <p style="color:var(--muted);font-size:14px"><?= h(t('no_groups_teacher')) ?></p>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px">
      <?php foreach ($groups as $g): ?>
      <a href="/teacher/group.php?id=<?= $g['id'] ?>" class="card card-link p5">
        <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:3px"><?= h($g['name']) ?></div>
        <?php if ($g['subject']): ?><div style="font-size:12px;color:var(--red);font-weight:600;margin-bottom:12px"><?= h($g['subject']) ?></div>
        <?php else: ?><div style="margin-bottom:12px"></div><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">
          <div class="stat" style="background:var(--bg-sub)"><div class="stat-value"><?= (int)$g['sc'] ?></div><div class="stat-label"><?= h(t('students_count')) ?></div></div>
          <div class="stat" style="background:var(--bg-sub)"><div class="stat-value"><?= (int)$g['lc'] ?></div><div class="stat-label"><?= h(t('lessons_count')) ?></div></div>
        </div>

        <div style="background:var(--bg-sub);border:1px solid var(--border);border-radius:7px;padding:7px 10px;display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:11px;color:var(--muted);font-weight:500"><?= h(t('invite_code')) ?></span>
          <span class="mono" style="font-size:14px;font-weight:700;color:var(--black);letter-spacing:.1em"><?= h($g['invite_code']) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
</div>
</body>
</html>

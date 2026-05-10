<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/lang.php';
require_once dirname(__DIR__) . '/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /teacher/'); exit; }
$user = requireAuth('teacher');
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $name = trim($_POST['group_name'] ?? '');
    $subj = trim($_POST['subject'] ?? '');
    if (!$name) { $error = t('err_required'); }
    else {
        $code = generateInviteCode();
        db()->prepare('INSERT INTO `groups` (name, subject, teacher_id, invite_code) VALUES (?,?,?,?)')->execute([$name, $subj ?: null, $user['id'], $code]);
        $success = t('saved_ok');
    }
}

$st = db()->prepare("
    SELECT g.*, COUNT(DISTINCT sg.student_id) AS sc, COUNT(DISTINCT l.id) AS lc
    FROM `groups` g
    LEFT JOIN student_groups sg ON sg.group_id=g.id
    LEFT JOIN lessons l ON l.group_id=g.id
    WHERE g.teacher_id=?
    GROUP BY g.id ORDER BY g.created_at DESC
");
$st->execute([$user['id']]);
$groups = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('teacher_dashboard')) ?> — <?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/teacher/') ?>

<div class="main">
  <div style="max-width:960px">

    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin-bottom:32px">
      <div>
        <h1 class="page-title"><?= h(t('teacher_dashboard')) ?></h1>
        <p class="page-sub"><?= h($user['name']) ?></p>
      </div>
      <button onclick="var cf=document.getElementById('cf');cf.style.display=cf.style.display===''?'none':''"
              class="btn btn-dark">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <?= h(t('create_group')) ?>
      </button>
    </div>

    <div id="cf" style="display:<?= ($error||$success)?'':'none' ?>;margin-bottom:24px">
      <div class="card card-p">
        <h2 style="font-size:16px;font-weight:800;color:#1A1510;margin:0 0 16px"><?= h(t('create_group')) ?></h2>
        <?php if ($error): ?><div class="alert-error" style="margin-bottom:12px"><?= h($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-success" style="margin-bottom:12px"><?= h($success) ?></div><?php endif; ?>
        <form method="POST">
          <div class="grid sm:grid-cols-2 gap-3" style="margin-bottom:12px">
            <div>
              <label class="label"><?= h(t('group_name')) ?> *</label>
              <input type="text" name="group_name" required class="input" placeholder="Английский A1 — утро">
            </div>
            <div>
              <label class="label"><?= h(t('subject')) ?></label>
              <input type="text" name="subject" class="input" placeholder="Английский язык">
            </div>
          </div>
          <div style="display:flex;gap:8px">
            <button type="submit" name="create_group" class="btn btn-dark"><?= h(t('btn_create')) ?></button>
            <button type="button" onclick="document.getElementById('cf').style.display='none'" class="btn btn-outline">
              <?= getLang()==='ru' ? 'Отмена' : (getLang()==='kz' ? 'Бас тарту' : 'Cancel') ?>
            </button>
          </div>
        </form>
      </div>
    </div>

    <?php if (empty($groups)): ?>
      <div class="card card-p" style="text-align:center;padding:64px 24px">
        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="1.5" style="margin:0 auto 16px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <p style="color:#7B6F5E;font-size:15px"><?= h(t('no_groups_teacher')) ?></p>
      </div>
    <?php else: ?>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($groups as $g): ?>
        <a href="/teacher/group.php?id=<?= $g['id'] ?>" style="text-decoration:none">
          <div class="card card-p card-hover" style="cursor:pointer;transition:all .15s">
            <h2 style="font-size:17px;font-weight:800;color:#1A1510;margin:0 0 3px"><?= h($g['name']) ?></h2>
            <?php if ($g['subject']): ?>
              <p style="font-size:13px;color:#C9A84C;font-weight:600;margin:0 0 14px"><?= h($g['subject']) ?></p>
            <?php else: ?><div style="margin-bottom:14px"></div><?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
              <div class="stat-box" style="background:#FAFAF8;border:1px solid #EDE5D4">
                <div class="num"><?= (int)$g['sc'] ?></div>
                <div class="lbl"><?= h(t('students_count')) ?></div>
              </div>
              <div class="stat-box" style="background:#FAFAF8;border:1px solid #EDE5D4">
                <div class="num"><?= (int)$g['lc'] ?></div>
                <div class="lbl"><?= h(t('lessons_count')) ?></div>
              </div>
            </div>

            <div style="background:#FAFAF8;border:1px solid #EDE5D4;border-radius:10px;padding:8px 12px;display:flex;align-items:center;justify-content:space-between">
              <span style="font-size:11px;color:#7B6F5E;font-weight:600"><?= h(t('invite_code')) ?></span>
              <span style="font-family:monospace;font-weight:900;font-size:15px;letter-spacing:.15em;color:#C9A84C"><?= h($g['invite_code']) ?></span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>
</body>
</html>

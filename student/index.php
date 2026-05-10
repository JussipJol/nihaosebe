<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/lang.php';
require_once dirname(__DIR__) . '/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /student/'); exit; }
$user = requireAuth('student');
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['join_code'] ?? ''));
    if (!$code) { $error = t('err_required'); }
    else {
        $st = db()->prepare('SELECT id FROM `groups` WHERE invite_code = ?');
        $st->execute([$code]);
        $g = $st->fetch();
        if (!$g) { $error = t('err_code_invalid'); }
        else {
            try {
                db()->prepare('INSERT INTO student_groups (student_id, group_id) VALUES (?, ?)')->execute([$user['id'], $g['id']]);
                $success = t('saved_ok');
            } catch (PDOException $e) { $error = t('err_already_member'); }
        }
    }
}

$st = db()->prepare("
    SELECT g.id, g.name, g.subject, u.name AS teacher_name,
           COUNT(DISTINCT l.id) AS total_lessons,
           SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS attended,
           SUM(CASE WHEN a.status='absent'  THEN 1 ELSE 0 END) AS missed
    FROM student_groups sg
    JOIN `groups` g ON g.id=sg.group_id
    JOIN users u ON u.id=g.teacher_id
    LEFT JOIN lessons l ON l.group_id=g.id
    LEFT JOIN attendance a ON a.lesson_id=l.id AND a.student_id=?
    WHERE sg.student_id=?
    GROUP BY g.id ORDER BY g.created_at DESC
");
$st->execute([$user['id'], $user['id']]);
$groups = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('student_dashboard')) ?> — <?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/student/') ?>

<div class="main">
  <div style="max-width:860px">

    <!-- Header -->
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin-bottom:32px">
      <div>
        <h1 class="page-title"><?= h(t('student_dashboard')) ?></h1>
        <p class="page-sub"><?= h($user['name']) ?></p>
      </div>
      <form method="POST" style="display:flex;gap:8px">
        <input type="text" name="join_code" maxlength="6"
               placeholder="<?= h(t('join_code_placeholder')) ?>"
               class="input" style="width:160px;text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:.1em">
        <button type="submit" class="btn btn-dark"><?= h(t('btn_join')) ?></button>
      </form>
    </div>

    <?php if ($error): ?><div class="alert-error" style="margin-bottom:16px"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-success" style="margin-bottom:16px">✓ <?= h($success) ?></div><?php endif; ?>

    <!-- Groups -->
    <?php if (empty($groups)): ?>
      <div class="card card-p" style="text-align:center;padding:64px 24px">
        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="1.5" style="margin:0 auto 16px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <p style="color:#7B6F5E;font-size:15px"><?= h(t('no_groups')) ?></p>
      </div>
    <?php else: ?>
      <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach ($groups as $g):
          $total = (int)$g['total_lessons'];
          $att   = (int)$g['attended'];
          $pct   = $total > 0 ? round($att / $total * 100) : 0;
          $pctColor = $pct >= 80 ? '#2D7A4F' : ($pct >= 60 ? '#9A6800' : '#C0392B');
          $pctBg    = $pct >= 80 ? '#EFFFF5' : ($pct >= 60 ? '#FFFBF0' : '#FFF5F5');
        ?>
        <a href="/student/group.php?id=<?= $g['id'] ?>" style="text-decoration:none">
          <div class="card card-p card-hover" style="cursor:pointer;transition:all .15s">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
              <div style="min-width:0">
                <h2 style="font-size:17px;font-weight:800;color:#1A1510;margin:0 0 3px"><?= h($g['name']) ?></h2>
                <?php if ($g['subject']): ?><p style="font-size:13px;color:#C9A84C;font-weight:600;margin:0 0 3px"><?= h($g['subject']) ?></p><?php endif; ?>
                <p style="font-size:12px;color:#7B6F5E;margin:0"><?= h(t('teacher')) ?>: <?= h($g['teacher_name']) ?></p>
              </div>
              <div style="background:<?= $pctBg ?>;color:<?= $pctColor ?>;border-radius:10px;padding:8px 12px;font-weight:900;font-size:18px;flex-shrink:0">
                <?= $pct ?>%
              </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px">
              <div class="stat-box" style="background:#FAFAF8;border:1px solid #EDE5D4">
                <div class="num"><?= $total ?></div>
                <div class="lbl"><?= h(t('total_lessons')) ?></div>
              </div>
              <div class="stat-box" style="background:#EFFFF5">
                <div class="num" style="color:#2D7A4F"><?= $att ?></div>
                <div class="lbl" style="color:#2D7A4F"><?= h(t('attended')) ?></div>
              </div>
              <div class="stat-box" style="background:#FFF5F5">
                <div class="num" style="color:#C0392B"><?= (int)$g['missed'] ?></div>
                <div class="lbl" style="color:#C0392B"><?= h(t('missed')) ?></div>
              </div>
            </div>

            <div style="background:#EDE5D4;border-radius:999px;height:5px;overflow:hidden">
              <div style="height:100%;width:<?= $pct ?>%;background:<?= $pctColor ?>;border-radius:999px;transition:width .3s"></div>
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

<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /student/group.php?id='.((int)$_GET['id'])); exit; }
$user = requireAuth('student');
$gid = (int)($_GET['id']??0);
if (!$gid) { header('Location: /student/'); exit; }

$st = db()->prepare('SELECT g.*,u.name AS tn FROM `groups` g JOIN users u ON u.id=g.teacher_id JOIN student_groups sg ON sg.group_id=g.id WHERE g.id=? AND sg.student_id=?');
$st->execute([$gid,$user['id']]);
$group = $st->fetch();
if (!$group) { header('Location: /student/'); exit; }

$st = db()->prepare("
    SELECT l.*,a.status AS my_att,
           h.id AS hid,h.title AS ht,h.description AS hd,h.due_date AS hdue,
           c.content AS cm
    FROM lessons l
    LEFT JOIN attendance a ON a.lesson_id=l.id AND a.student_id=?
    LEFT JOIN homework h ON h.lesson_id=l.id
    LEFT JOIN comments c ON c.lesson_id=l.id AND c.student_id=?
    WHERE l.group_id=? ORDER BY l.lesson_date DESC
");
$st->execute([$user['id'],$user['id'],$gid]);
$lessons = $st->fetchAll();

$attBadge = ['present'=>'badge-green','absent'=>'badge-red','late'=>'badge-amber'];
$attLabel = ['present'=>t('present'),'absent'=>t('absent'),'late'=>t('late')];
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($group['name']) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/student/', 'id='.$gid) ?>
<div class="main">
<div class="content" style="max-width:760px">

  <a href="/student/" class="back-link">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    <?= h(t('back_to_dashboard')) ?>
  </a>

  <div style="margin-bottom:24px">
    <h1 class="page-title"><?= h($group['name']) ?></h1>
    <?php if ($group['subject']): ?><div style="font-size:13px;color:var(--red);font-weight:600;margin-top:2px"><?= h($group['subject']) ?></div><?php endif; ?>
    <p class="page-sub"><?= h(t('teacher')) ?>: <?= h($group['tn']) ?></p>
  </div>

  <?php if (empty($lessons)): ?>
    <div class="card p5" style="text-align:center;color:var(--muted)"><?= h(t('no_lessons')) ?></div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:10px">
      <?php foreach ($lessons as $l):
        $att = $l['my_att'] ?? 'absent';
        $bc  = $attBadge[$att] ?? 'badge-red';
        $bl  = $attLabel[$att] ?? $att;
      ?>
      <div class="card" style="overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border)">
          <div>
            <div style="font-size:15px;font-weight:700;color:var(--black)"><?= h($l['title']) ?></div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px"><?= date('d.m.Y',strtotime($l['lesson_date'])) ?></div>
          </div>
          <span class="badge <?= $bc ?>"><?= h($bl) ?></span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:none">
          <div style="padding:14px 18px;border-right:1px solid var(--border)">
            <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px"><?= h(t('homework')) ?></div>
            <?php if ($l['hid']): ?>
              <div style="font-size:14px;font-weight:600;color:var(--black);margin-bottom:3px"><?= h($l['ht']) ?></div>
              <?php if ($l['hd']): ?><div style="font-size:12px;color:var(--muted);margin-bottom:3px"><?= h($l['hd']) ?></div><?php endif; ?>
              <?php if ($l['hdue']): ?><div style="font-size:12px;color:var(--red);font-weight:500"><?= h(t('due_date')) ?>: <?= date('d.m.Y',strtotime($l['hdue'])) ?></div><?php endif; ?>
            <?php else: ?>
              <div style="font-size:13px;color:#D4D4D8"><?= h(t('no_homework')) ?></div>
            <?php endif; ?>
          </div>
          <div style="padding:14px 18px">
            <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px"><?= h(t('teacher_comment')) ?></div>
            <?php if ($l['cm']): ?>
              <div style="font-size:14px;color:var(--black);line-height:1.55"><?= h($l['cm']) ?></div>
            <?php else: ?>
              <div style="font-size:13px;color:#D4D4D8"><?= h(t('no_comment')) ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
</div>
</body>
</html>

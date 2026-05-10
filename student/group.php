<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/lang.php';
require_once dirname(__DIR__) . '/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /student/group.php?id='.((int)$_GET['id'])); exit; }
$user = requireAuth('student');

$groupId = (int)($_GET['id'] ?? 0);
if (!$groupId) { header('Location: /student/'); exit; }

$st = db()->prepare('SELECT g.*,u.name AS teacher_name FROM `groups` g JOIN users u ON u.id=g.teacher_id JOIN student_groups sg ON sg.group_id=g.id WHERE g.id=? AND sg.student_id=?');
$st->execute([$groupId, $user['id']]);
$group = $st->fetch();
if (!$group) { header('Location: /student/'); exit; }

$st = db()->prepare("
    SELECT l.*, a.status AS my_att,
           h.id AS hw_id, h.title AS hw_title, h.description AS hw_desc, h.due_date AS hw_due,
           c.content AS comment
    FROM lessons l
    LEFT JOIN attendance a ON a.lesson_id=l.id AND a.student_id=?
    LEFT JOIN homework h ON h.lesson_id=l.id
    LEFT JOIN comments c ON c.lesson_id=l.id AND c.student_id=?
    WHERE l.group_id=?
    ORDER BY l.lesson_date DESC
");
$st->execute([$user['id'], $user['id'], $groupId]);
$lessons = $st->fetchAll();

$attMap = [
    'present' => ['label'=>t('present'), 'class'=>'att-present'],
    'absent'  => ['label'=>t('absent'),  'class'=>'att-absent'],
    'late'    => ['label'=>t('late'),    'class'=>'att-late'],
];
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
<?= sidebar($user, '/student/', 'id='.$groupId) ?>

<div class="main">
  <div style="max-width:760px">

    <a href="/student/" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:#9A7A2A;text-decoration:none;margin-bottom:20px">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      <?= h(t('back_to_dashboard')) ?>
    </a>

    <div style="margin-bottom:28px">
      <h1 class="page-title"><?= h($group['name']) ?></h1>
      <?php if ($group['subject']): ?><p style="color:#C9A84C;font-weight:700;font-size:15px;margin:3px 0"><?= h($group['subject']) ?></p><?php endif; ?>
      <p class="page-sub"><?= h(t('teacher')) ?>: <?= h($group['teacher_name']) ?></p>
    </div>

    <?php if (empty($lessons)): ?>
      <div class="card card-p" style="text-align:center;padding:48px"><p style="color:#7B6F5E"><?= h(t('no_lessons')) ?></p></div>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:12px">
        <?php foreach ($lessons as $l):
          $att = $l['my_att'] ?? 'absent';
          $attInfo = $attMap[$att] ?? $attMap['absent'];
        ?>
        <div class="card" style="overflow:hidden">
          <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #EDE5D4">
            <div>
              <p style="font-size:16px;font-weight:800;color:#1A1510;margin:0 0 2px"><?= h($l['title']) ?></p>
              <p style="font-size:12px;color:#7B6F5E;margin:0"><?= date('d.m.Y', strtotime($l['lesson_date'])) ?></p>
            </div>
            <span class="att-badge <?= $attInfo['class'] ?>"><?= h($attInfo['label']) ?></span>
          </div>
          <div class="grid sm:grid-cols-2">
            <div style="padding:16px 20px;border-right:1px solid #EDE5D4">
              <p style="font-size:11px;font-weight:700;letter-spacing:.1em;color:#C9A84C;text-transform:uppercase;margin:0 0 10px"><?= h(t('homework')) ?></p>
              <?php if ($l['hw_id']): ?>
                <p style="font-size:14px;font-weight:700;color:#1A1510;margin:0 0 4px"><?= h($l['hw_title']) ?></p>
                <?php if ($l['hw_desc']): ?><p style="font-size:13px;color:#7B6F5E;margin:0 0 4px"><?= h($l['hw_desc']) ?></p><?php endif; ?>
                <?php if ($l['hw_due']): ?><p style="font-size:12px;color:#9A7A2A;font-weight:600;margin:0"><?= h(t('due_date')) ?>: <?= date('d.m.Y', strtotime($l['hw_due'])) ?></p><?php endif; ?>
              <?php else: ?>
                <p style="font-size:13px;color:#C0C0B0"><?= h(t('no_homework')) ?></p>
              <?php endif; ?>
            </div>
            <div style="padding:16px 20px">
              <p style="font-size:11px;font-weight:700;letter-spacing:.1em;color:#C9A84C;text-transform:uppercase;margin:0 0 10px"><?= h(t('teacher_comment')) ?></p>
              <?php if ($l['comment']): ?>
                <p style="font-size:14px;color:#1A1510;line-height:1.5;margin:0"><?= h($l['comment']) ?></p>
              <?php else: ?>
                <p style="font-size:13px;color:#C0C0B0"><?= h(t('no_comment')) ?></p>
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

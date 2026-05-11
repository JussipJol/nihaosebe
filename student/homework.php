<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /student/homework.php'); exit; }
$user = requireAuth('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_hw_done'])) {
    $hwId = (int)($_POST['hw_id'] ?? 0);
    if ($hwId) {
        $ex = db()->prepare('SELECT id FROM hw_done WHERE homework_id=? AND student_id=?');
        $ex->execute([$hwId, $user['id']]);
        if ($ex->fetch()) {
            db()->prepare('DELETE FROM hw_done WHERE homework_id=? AND student_id=?')->execute([$hwId, $user['id']]);
        } else {
            db()->prepare('INSERT IGNORE INTO hw_done (homework_id,student_id) VALUES (?,?)')->execute([$hwId, $user['id']]);
        }
    }
    header('Location: /student/homework.php'); exit;
}

$st = db()->prepare("
    SELECT h.id, h.title, h.description, h.due_date,
           l.id AS lid, l.lesson_date,
           g.id AS gid, g.name AS gname, g.hsk_level,
           hwd.id AS done_id
    FROM student_groups sg
    JOIN `groups` g      ON g.id = sg.group_id
    JOIN lessons l       ON l.group_id = g.id
    JOIN homework h      ON h.lesson_id = l.id
    LEFT JOIN hw_done hwd ON hwd.homework_id = h.id AND hwd.student_id = ?
    WHERE sg.student_id = ?
    ORDER BY hwd.id IS NOT NULL ASC, h.due_date IS NULL ASC, h.due_date ASC, l.lesson_date DESC
");
$st->execute([$user['id'], $user['id']]);
$all = $st->fetchAll();

$pending = array_values(array_filter($all, fn($h) => !$h['done_id']));
$done    = array_values(array_filter($all, fn($h) =>  $h['done_id']));

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('my_homework')) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/student/homework.php') ?>
<div class="main">
<div class="content" style="max-width:680px">

  <div style="margin-bottom:24px">
    <h1 class="page-title"><?= h(t('my_homework')) ?></h1>
    <p class="page-sub"><?= count($pending) ?> не выполнено · <?= count($done) ?> выполнено</p>
  </div>

  <?php if (empty($all)): ?>
    <div class="card p5" style="text-align:center;color:var(--muted);padding:48px 24px">
      <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#D4D4D8" stroke-width="1.5" style="margin:0 auto 12px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      <?= h(t('no_homework_all')) ?>
    </div>
  <?php else: ?>

    <!-- Pending -->
    <?php if (!empty($pending)): ?>
    <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px"><?= h(t('hw_pending')) ?> (<?= count($pending) ?>)</div>
    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:24px">
      <?php foreach ($pending as $h):
        $overdue = $h['due_date'] && $h['due_date'] < $today;
        $soon    = $h['due_date'] && $h['due_date'] >= $today && $h['due_date'] <= date('Y-m-d', strtotime('+3 days'));
      ?>
      <div class="card" style="display:flex;align-items:stretch;overflow:hidden">
        <div style="width:4px;background:<?= $overdue?'var(--red)':($soon?'var(--amber)':'var(--border)') ?>;flex-shrink:0"></div>
        <div style="flex:1;padding:14px 16px">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div>
              <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:3px"><?= h($h['title']) ?></div>
              <?php if ($h['description']): ?>
                <div style="font-size:13px;color:var(--muted);margin-bottom:4px"><?= h($h['description']) ?></div>
              <?php endif; ?>
              <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:4px">
                <a href="/student/group.php?id=<?= $h['gid'] ?>" style="text-decoration:none">
                  <span style="font-size:12px;color:var(--muted)"><?= h($h['gname']) ?></span>
                  <?php if ($h['hsk_level']): ?>
                    <span style="background:var(--black);color:#fff;border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700;margin-left:4px">HSK <?= (int)$h['hsk_level'] ?></span>
                  <?php endif; ?>
                </a>
                <span style="color:var(--border)">·</span>
                <span style="font-size:12px;color:var(--muted)"><?= date('d.m.Y', strtotime($h['lesson_date'])) ?></span>
                <?php if ($h['due_date']): ?>
                  <span style="color:var(--border)">·</span>
                  <span style="font-size:12px;font-weight:600;color:<?= $overdue?'var(--red)':($soon?'var(--amber)':'var(--muted)') ?>">
                    <?= $overdue?'Просрочено: ':h(t('due_date')).': ' ?><?= date('d.m.Y', strtotime($h['due_date'])) ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>
            <form method="POST" style="flex-shrink:0">
              <input type="hidden" name="toggle_hw_done" value="1">
              <input type="hidden" name="hw_id" value="<?= $h['id'] ?>">
              <button type="submit" class="btn btn-outline btn-sm"><?= h(t('hw_mark_done')) ?></button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Done -->
    <?php if (!empty($done)): ?>
    <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px"><?= h(t('hw_completed')) ?> (<?= count($done) ?>)</div>
    <div style="display:flex;flex-direction:column;gap:6px">
      <?php foreach ($done as $h): ?>
      <div class="card" style="display:flex;align-items:stretch;overflow:hidden;opacity:.65">
        <div style="width:4px;background:var(--green);flex-shrink:0"></div>
        <div style="flex:1;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <div>
            <div style="font-size:14px;font-weight:600;color:var(--black);text-decoration:line-through;margin-bottom:2px"><?= h($h['title']) ?></div>
            <span style="font-size:12px;color:var(--muted)"><?= h($h['gname']) ?></span>
          </div>
          <form method="POST" style="flex-shrink:0">
            <input type="hidden" name="toggle_hw_done" value="1">
            <input type="hidden" name="hw_id" value="<?= $h['id'] ?>">
            <button type="submit" class="btn btn-sm" style="background:var(--green-bg);color:var(--green);border:1px solid #BBF7D0;font-weight:600">
              ✓ <?= h(t('hw_done')) ?>
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  <?php endif; ?>

</div>
</div>
</body>
</html>

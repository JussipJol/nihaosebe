<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /student/'); exit; }
$user = requireAuth('student');
$error = $success = '';

// Вступить в группу
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

// Общая статистика
$st = db()->prepare("
    SELECT
        COUNT(DISTINCT sg.group_id)                                         AS groups_count,
        COUNT(DISTINCT l.id)                                                AS total_lessons,
        SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END)                AS attended,
        SUM(CASE WHEN a.status='absent'  THEN 1 ELSE 0 END)                AS missed,
        SUM(CASE WHEN a.status='late'    THEN 1 ELSE 0 END)                AS late_count,
        COUNT(DISTINCT CASE WHEN h.id IS NOT NULL THEN l.id END)           AS hw_total,
        COUNT(DISTINCT CASE WHEN c.id IS NOT NULL THEN l.id END)           AS comments_total
    FROM student_groups sg
    LEFT JOIN lessons l    ON l.group_id = sg.group_id
    LEFT JOIN attendance a ON a.lesson_id = l.id AND a.student_id = ?
    LEFT JOIN homework h   ON h.lesson_id = l.id
    LEFT JOIN comments c   ON c.lesson_id = l.id AND c.student_id = ?
    WHERE sg.student_id = ?
");
$st->execute([$user['id'], $user['id'], $user['id']]);
$stats = $st->fetch();

$totalLessons = (int)$stats['total_lessons'];
$attended     = (int)$stats['attended'];
$attPct       = $totalLessons > 0 ? round($attended / $totalLessons * 100) : 0;
$attColor     = $attPct >= 80 ? 'var(--green)' : ($attPct >= 60 ? 'var(--amber)' : 'var(--red)');

// Последние уроки (8 штук)
$st = db()->prepare("
    SELECT l.id, l.title, l.lesson_date,
           g.name AS gname, g.hsk_level,
           a.status AS my_att,
           h.title AS hw_title, h.due_date AS hw_due,
           c.content AS comment
    FROM lessons l
    JOIN `groups` g          ON g.id = l.group_id
    JOIN student_groups sg   ON sg.group_id = g.id AND sg.student_id = ?
    LEFT JOIN attendance a   ON a.lesson_id = l.id AND a.student_id = ?
    LEFT JOIN homework h     ON h.lesson_id = l.id
    LEFT JOIN comments c     ON c.lesson_id = l.id AND c.student_id = ?
    ORDER BY l.lesson_date DESC, l.id DESC
    LIMIT 8
");
$st->execute([$user['id'], $user['id'], $user['id']]);
$recentLessons = $st->fetchAll();

// Мои группы
$st = db()->prepare("
    SELECT g.id, g.name, g.hsk_level, g.schedule, g.lesson_time, u.name AS tn,
           COUNT(DISTINCT l.id) AS tl,
           SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS att,
           SUM(CASE WHEN a.status='absent'  THEN 1 ELSE 0 END) AS msd
    FROM student_groups sg
    JOIN `groups` g    ON g.id = sg.group_id
    JOIN users u       ON u.id = g.teacher_id
    LEFT JOIN lessons l     ON l.group_id = g.id
    LEFT JOIN attendance a  ON a.lesson_id = l.id AND a.student_id = ?
    WHERE sg.student_id = ?
    GROUP BY g.id, g.hsk_level, g.schedule, g.lesson_time
    ORDER BY g.created_at DESC
");
$st->execute([$user['id'], $user['id']]);
$groups = $st->fetchAll();

$attBadge = ['present'=>'badge-green','absent'=>'badge-red','late'=>'badge-amber'];
$attLabel = ['present'=>t('present'),'absent'=>t('absent'),'late'=>t('late')];
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('student_dashboard')) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
<style>
.section-title {
  font-size: 13px; font-weight: 700; color: var(--muted);
  text-transform: uppercase; letter-spacing: .07em;
  margin-bottom: 12px; margin-top: 28px;
}
.section-title:first-of-type { margin-top: 0; }
</style>
</head>
<body>
<?= sidebar($user, '/student/') ?>
<div class="main">
<div class="content">

  <!-- Шапка -->
  <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:24px">
    <div>
      <h1 class="page-title"><?= h($user['name']) ?></h1>
      <p class="page-sub"><?= h(t('role_student')) ?> · <?= APP_NAME ?></p>
    </div>
    <button onclick="document.getElementById('joinModal').style.display='flex'" class="btn btn-outline">
      <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      <?= h(t('join_group')) ?>
    </button>
  </div>

  <!-- Статистика -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:6px">
    <div class="card p5">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px"><?= h(t('groups_label')) ?></div>
      <div style="font-size:28px;font-weight:800;color:var(--black)"><?= (int)$stats['groups_count'] ?></div>
    </div>
    <div class="card p5">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px"><?= h(t('total_lessons')) ?></div>
      <div style="font-size:28px;font-weight:800;color:var(--black)"><?= $totalLessons ?></div>
    </div>
    <div class="card p5">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px"><?= h(t('attendance_pct')) ?></div>
      <div style="font-size:28px;font-weight:800;color:<?= $attColor ?>"><?= $attPct ?>%</div>
      <div style="margin-top:8px">
        <div class="progress">
          <div class="progress-fill <?= $attPct>=80?'progress-green':($attPct>=60?'progress-amber':'progress-red') ?>" style="width:<?= $attPct ?>%"></div>
        </div>
      </div>
    </div>
    <div class="card p5">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px"><?= h(t('missed')) ?></div>
      <div style="font-size:28px;font-weight:800;color:var(--red)"><?= (int)$stats['missed'] ?></div>
    </div>
  </div>

  <!-- Последние уроки -->
  <div class="section-title"><?= h(t('recent_lessons')) ?></div>

  <?php if (empty($recentLessons)): ?>
    <div class="card p5" style="text-align:center;color:var(--muted);font-size:14px;padding:32px"><?= h(t('no_lessons')) ?></div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:8px">
      <?php foreach ($recentLessons as $l):
        $att  = $l['my_att'] ?? null;
        $bc   = $att ? ($attBadge[$att] ?? 'badge-red') : null;
        $bl   = $att ? ($attLabel[$att] ?? '') : null;
      ?>
      <div class="card" style="overflow:hidden">
        <!-- Строка урока -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;gap:12px">
          <div style="display:flex;align-items:center;gap:12px;min-width:0">
            <!-- Дата -->
            <div style="text-align:center;background:var(--bg-sub);border:1px solid var(--border);border-radius:8px;padding:6px 10px;flex-shrink:0">
              <div style="font-size:16px;font-weight:800;color:var(--black);line-height:1"><?= date('d', strtotime($l['lesson_date'])) ?></div>
              <div style="font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase"><?= date('M', strtotime($l['lesson_date'])) ?></div>
            </div>
            <!-- Тема + группа -->
            <div style="min-width:0">
              <div style="font-size:14px;font-weight:700;color:var(--black);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($l['title']) ?></div>
              <div style="display:flex;align-items:center;gap:6px">
                <span style="font-size:12px;color:var(--muted)"><?= h($l['gname']) ?></span>
                <?php if ($l['hsk_level']): ?>
                  <span style="background:var(--black);color:#fff;border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700">HSK <?= (int)$l['hsk_level'] ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <!-- Посещаемость -->
          <?php if ($bc): ?>
            <span class="badge <?= $bc ?>" style="flex-shrink:0"><?= h($bl) ?></span>
          <?php else: ?>
            <span style="font-size:12px;color:var(--border)">—</span>
          <?php endif; ?>
        </div>

        <!-- ДЗ и комментарий (если есть) -->
        <?php if ($l['hw_title'] || $l['comment']): ?>
        <div style="display:flex;border-top:1px solid var(--border)">
          <?php if ($l['hw_title']): ?>
          <div style="flex:1;padding:10px 16px;<?= $l['comment'] ? 'border-right:1px solid var(--border)' : '' ?>">
            <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px"><?= h(t('hw_short')) ?></div>
            <div style="font-size:13px;font-weight:600;color:var(--black)"><?= h($l['hw_title']) ?></div>
            <?php if ($l['hw_due']): ?>
              <div style="font-size:11px;color:var(--red);margin-top:2px">до <?= date('d.m', strtotime($l['hw_due'])) ?></div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php if ($l['comment']): ?>
          <div style="flex:1;padding:10px 16px">
            <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px"><?= h(t('teacher_comment')) ?></div>
            <div style="font-size:13px;color:var(--black);line-height:1.4"><?= h($l['comment']) ?></div>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Мои группы -->
  <?php if (!empty($groups)): ?>
  <div class="section-title"><?= h(t('my_groups')) ?></div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px">
    <?php foreach ($groups as $g):
      $tl  = (int)$g['tl'];
      $att = (int)$g['att'];
      $pct = $tl > 0 ? round($att/$tl*100) : 0;
      $bc2 = $pct>=80?'progress-green':($pct>=60?'progress-amber':'progress-red');
      $sc  = $pct>=80?'badge-green':($pct>=60?'badge-amber':'badge-red');
    ?>
    <a href="/student/group.php?id=<?= $g['id'] ?>" class="card card-link p5">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
        <div>
          <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap">
            <span style="font-size:14px;font-weight:700;color:var(--black)"><?= h($g['name']) ?></span>
            <?php if ($g['hsk_level']): ?>
              <span style="background:var(--black);color:#fff;border-radius:5px;padding:2px 6px;font-size:10px;font-weight:700">HSK <?= (int)$g['hsk_level'] ?></span>
            <?php endif; ?>
          </div>
          <?php if ($g['schedule'] || $g['lesson_time']): ?>
          <div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:4px;align-items:center">
            <?php if ($g['schedule']): foreach (explode(',', $g['schedule']) as $d): ?>
              <span style="background:var(--bg-sub);border:1px solid var(--border);border-radius:4px;padding:1px 5px;font-size:10px;font-weight:600;color:var(--muted)"><?= h($d) ?></span>
            <?php endforeach; endif; ?>
            <?php if ($g['lesson_time']): ?>
              <span style="font-size:11px;color:var(--muted);font-weight:500"><?= h($g['lesson_time']) ?></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <span class="badge <?= $sc ?>" style="flex-shrink:0"><?= $pct ?>%</span>
      </div>
      <div style="display:flex;gap:8px;margin-bottom:8px">
        <div class="stat" style="background:var(--bg-sub);flex:1;padding:8px 10px">
          <div style="font-size:18px;font-weight:800;color:var(--black)"><?= $tl ?></div>
          <div style="font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:2px"><?= h(t('total_lessons')) ?></div>
        </div>
        <div class="stat" style="background:var(--green-bg);flex:1;padding:8px 10px">
          <div style="font-size:18px;font-weight:800;color:var(--green)"><?= $att ?></div>
          <div style="font-size:10px;font-weight:600;color:var(--green);text-transform:uppercase;letter-spacing:.05em;margin-top:2px"><?= h(t('attended')) ?></div>
        </div>
        <div class="stat" style="background:var(--red-bg);flex:1;padding:8px 10px">
          <div style="font-size:18px;font-weight:800;color:var(--red)"><?= (int)$g['msd'] ?></div>
          <div style="font-size:10px;font-weight:600;color:var(--red);text-transform:uppercase;letter-spacing:.05em;margin-top:2px"><?= h(t('missed')) ?></div>
        </div>
      </div>
      <div class="progress">
        <div class="progress-fill <?= $bc2 ?>" style="width:<?= $pct ?>%"></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
</div>

<!-- Modal: Вступить в группу -->
<div id="joinModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
  <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.15)">
    <div style="font-size:16px;font-weight:700;color:var(--black);margin-bottom:16px"><?= h(t('join_group')) ?></div>
    <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:12px"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:12px">✓ <?= h($success) ?></div><?php endif; ?>
    <form method="POST" style="display:flex;flex-direction:column;gap:12px">
      <div>
        <label class="label"><?= h(t('join_code_label')) ?></label>
        <input type="text" name="join_code" maxlength="6" class="input mono"
               style="text-transform:uppercase;letter-spacing:.15em;font-size:18px;font-weight:700;text-align:center"
               placeholder="XXXXXX" autofocus>
        <p style="font-size:11px;color:var(--muted);margin-top:5px"><?= h(t('join_code_hint')) ?></p>
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-black"><?= h(t('btn_join')) ?></button>
        <button type="button" onclick="document.getElementById('joinModal').style.display='none'" class="btn btn-ghost"><?= h(t('cancel')) ?></button>
      </div>
    </form>
  </div>
</div>

<script>
<?php if ($error || $success): ?>
document.getElementById('joinModal').style.display = 'flex';
<?php endif; ?>
</script>

</body>
</html>

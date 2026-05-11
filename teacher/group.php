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

$days = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];

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

    } elseif (isset($_POST['edit_group'])) {
        $name      = trim($_POST['group_name'] ?? '');
        $hsk       = (int)($_POST['hsk_level'] ?? 0);
        $schedule  = implode(',', array_filter($_POST['schedule'] ?? [], fn($d) => in_array($d, $days)));
        $timeFrom  = $_POST['time_from'] ?? '';
        $timeTo    = $_POST['time_to'] ?? '';
        $lessonTime = ($timeFrom && $timeTo) ? "{$timeFrom} – {$timeTo}" : null;
        if (!$name) { $error = t('err_required'); }
        else {
            db()->prepare('UPDATE `groups` SET name=?,hsk_level=?,schedule=?,lesson_time=? WHERE id=? AND teacher_id=?')
                ->execute([$name, $hsk ?: null, $schedule ?: null, $lessonTime, $gid, $user['id']]);
            $success = t('group_updated');
            $st = db()->prepare('SELECT * FROM `groups` WHERE id=? AND teacher_id=?');
            $st->execute([$gid, $user['id']]);
            $group = $st->fetch();
        }

    } elseif (isset($_POST['delete_lesson'])) {
        $lid = (int)($_POST['lesson_id'] ?? 0);
        if ($lid) {
            $chk = db()->prepare('SELECT id FROM lessons WHERE id=? AND group_id=?');
            $chk->execute([$lid, $gid]);
            if ($chk->fetch()) {
                db()->prepare('DELETE FROM lessons WHERE id=?')->execute([$lid]);
                $success = t('saved_ok');
            }
        }

    } elseif (isset($_POST['remove_student'])) {
        $sid = (int)($_POST['student_id'] ?? 0);
        if ($sid) {
            db()->prepare('DELETE FROM student_groups WHERE student_id=? AND group_id=?')->execute([$sid, $gid]);
        }

    } elseif (isset($_POST['delete_group'])) {
        db()->prepare('DELETE FROM `groups` WHERE id=? AND teacher_id=?')->execute([$gid,$user['id']]);
        header('Location: /teacher/'); exit;
    }
}

// Parse existing lesson_time into time_from/time_to for the edit form
$existingTimeFrom = $existingTimeTo = '';
if ($group['lesson_time']) {
    $parts = preg_split('/\s*[–—\-]\s*/', $group['lesson_time'], 2);
    if (count($parts) === 2) { $existingTimeFrom = trim($parts[0]); $existingTimeTo = trim($parts[1]); }
}
$existingDays = $group['schedule'] ? array_map('trim', explode(',', $group['schedule'])) : [];

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
<style>
.day-check { display: none; }
.day-label {
  display: inline-flex; align-items: center; justify-content: center;
  width: 34px; height: 34px; border-radius: 7px; font-size: 12px; font-weight: 700;
  border: 1.5px solid var(--border); cursor: pointer; color: var(--muted);
  transition: all .12s; user-select: none;
}
.day-check:checked + .day-label { background: var(--black); color: #fff; border-color: var(--black); }
</style>
</head>
<body>
<?= sidebar($user, '/teacher/', 'id='.$gid) ?>
<div class="main">
<div class="content">

  <a href="/teacher/" class="back-link">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    <?= h(t('back_to_dashboard')) ?>
  </a>

  <!-- Group header -->
  <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:20px">
    <div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px">
        <h1 class="page-title" style="margin-bottom:0"><?= h($group['name']) ?></h1>
        <?php if ($group['hsk_level']): ?>
          <span style="background:var(--black);color:#fff;border-radius:7px;padding:4px 10px;font-size:13px;font-weight:700">HSK <?= (int)$group['hsk_level'] ?></span>
        <?php endif; ?>
      </div>
      <?php if ($group['schedule'] || $group['lesson_time']): ?>
      <div style="display:flex;flex-wrap:wrap;align-items:center;gap:5px">
        <?php if ($group['schedule']): foreach (explode(',', $group['schedule']) as $d): ?>
          <span style="background:var(--bg-sub);border:1px solid var(--border);border-radius:5px;padding:2px 7px;font-size:11px;font-weight:600;color:var(--muted)"><?= h($d) ?></span>
        <?php endforeach; endif; ?>
        <?php if ($group['lesson_time']): ?>
          <span style="font-size:13px;font-weight:600;color:var(--muted)"><?= h($group['lesson_time']) ?></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;flex-shrink:0">
      <a href="/teacher/attendance.php?id=<?= $gid ?>" class="btn btn-outline btn-sm">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <?= h(t('attendance_overview')) ?>
      </a>
      <button onclick="var f=document.getElementById('editForm');f.style.display=f.style.display===''?'none':''" class="btn btn-outline btn-sm">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        <?= h(t('edit_group')) ?>
      </button>
      <div style="background:var(--bg-sub);border:1px solid var(--border);border-radius:8px;padding:7px 12px;display:flex;align-items:center;gap:8px">
        <span style="font-size:11px;color:var(--muted);font-weight:500"><?= h(t('invite_code')) ?></span>
        <span class="mono" style="font-size:17px;font-weight:800;color:var(--black);letter-spacing:.15em"><?= h($group['invite_code']) ?></span>
      </div>
    </div>
  </div>

  <!-- Edit group form -->
  <div id="editForm" style="display:none;margin-bottom:16px">
    <div class="card p5">
      <div style="font-size:14px;font-weight:700;color:var(--black);margin-bottom:14px"><?= h(t('edit_group')) ?></div>
      <form method="POST" style="display:flex;flex-direction:column;gap:12px">
        <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end">
          <div>
            <label class="label"><?= h(t('group_name')) ?> *</label>
            <input type="text" name="group_name" required class="input" value="<?= h($group['name']) ?>">
          </div>
          <div>
            <label class="label">HSK</label>
            <select name="hsk_level" class="input" style="width:auto">
              <option value="0" <?= !$group['hsk_level']?'selected':'' ?>><?= h(t('hsk_none')) ?></option>
              <?php for ($i=1;$i<=6;$i++): ?>
                <option value="<?= $i ?>" <?= $group['hsk_level']==$i?'selected':'' ?>>HSK <?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div>
          <label class="label"><?= getLang()==='ru'?'Дни недели':(getLang()==='kz'?'Апта күндері':'Days') ?></label>
          <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:4px">
            <?php foreach ($days as $d): ?>
            <div>
              <input type="checkbox" name="schedule[]" value="<?= $d ?>" id="ed_<?= $d ?>" class="day-check"
                     <?= in_array($d,$existingDays)?'checked':'' ?>>
              <label for="ed_<?= $d ?>" class="day-label"><?= $d ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div>
          <label class="label"><?= getLang()==='ru'?'Время занятий':(getLang()==='kz'?'Сабақ уақыты':'Time') ?></label>
          <div style="display:flex;align-items:center;gap:8px">
            <input type="time" name="time_from" class="input" style="width:130px" value="<?= h($existingTimeFrom) ?>">
            <span style="color:var(--muted)">—</span>
            <input type="time" name="time_to" class="input" style="width:130px" value="<?= h($existingTimeTo) ?>">
          </div>
        </div>
        <div style="display:flex;gap:8px">
          <button type="submit" name="edit_group" class="btn btn-black"><?= h(t('btn_save')) ?></button>
          <button type="button" onclick="document.getElementById('editForm').style.display='none'" class="btn btn-ghost"><?= h(t('cancel')) ?></button>
        </div>
      </form>
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
          <div style="display:flex;align-items:stretch;gap:6px">
            <a href="/teacher/lesson.php?id=<?= $l['id'] ?>" class="card card-link" style="flex:1;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px">
              <div>
                <div style="font-size:14px;font-weight:600;color:var(--black)"><?= h($l['title']) ?></div>
                <div style="font-size:12px;color:var(--muted);margin-top:1px"><?= date('d.m.Y',strtotime($l['lesson_date'])) ?></div>
              </div>
              <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:14px;font-weight:700;color:<?= $pc ?>"><?= $pct ?>%</span>
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#D4D4D8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
              </div>
            </a>
            <form method="POST" onsubmit="return confirm('<?= h(t('confirm_del_lesson')) ?>')" style="display:flex">
              <input type="hidden" name="delete_lesson" value="1">
              <input type="hidden" name="lesson_id" value="<?= $l['id'] ?>">
              <button type="submit" class="btn btn-danger" style="border-radius:10px;padding:0 12px;align-self:stretch" title="<?= h(t('delete_lesson')) ?>">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </form>
          </div>
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
              <div style="min-width:0;flex:1">
                <div style="font-size:13px;font-weight:600;color:var(--black);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($s['name']) ?></div>
                <div style="font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:monospace"><?= h($s['login']) ?></div>
              </div>
              <form method="POST" onsubmit="return confirm('<?= h(t('confirm_remove_student')) ?>')" style="flex-shrink:0">
                <input type="hidden" name="remove_student" value="1">
                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--muted);padding:3px 6px;font-size:16px;line-height:1" title="<?= h(t('remove_from_group')) ?>">×</button>
              </form>
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
<?php if ($success && isset($_POST['edit_group'])): ?>
<script>document.getElementById('editForm').style.display='';</script>
<?php endif; ?>
</body>
</html>

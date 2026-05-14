<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /teacher/'); exit; }
$user = requireAuth('teacher');
$error = $success = '';

$days = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $name      = trim($_POST['group_name'] ?? '');
    $hsk       = (int)($_POST['hsk_level'] ?? 0);
    $schedule  = implode(',', array_filter($_POST['schedule'] ?? [], fn($d) => in_array($d, $days)));
    $timeFrom  = $_POST['time_from'] ?? '';
    $timeTo    = $_POST['time_to'] ?? '';
    $lessonTime = ($timeFrom && $timeTo) ? "{$timeFrom} – {$timeTo}" : null;

    if (!$name) { $error = t('err_required'); }
    else {
        $code = generateInviteCode();
        db()->prepare('INSERT INTO `groups` (name,hsk_level,schedule,lesson_time,teacher_id,invite_code) VALUES (?,?,?,?,?,?)')
            ->execute([$name, $hsk ?: null, $schedule ?: null, $lessonTime, $user['id'], $code]);
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
$showForm = $error || $success;
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('teacher_dashboard')) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
<style>
.day-check { display: none; }
.day-label {
  display: inline-flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 8px; font-size: 12px; font-weight: 700;
  border: 1.5px solid var(--border); cursor: pointer; color: var(--muted);
  transition: all .12s; user-select: none;
}
.day-check:checked + .day-label { background: var(--black); color: #fff; border-color: var(--black); }
.day-pill {
  display: inline-flex; align-items: center; padding: 2px 7px;
  border-radius: 5px; font-size: 11px; font-weight: 600;
  background: var(--bg-sub); border: 1px solid var(--border); color: var(--muted);
}
</style>
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

  <!-- Форма создания группы -->
  <div id="cf" style="display:<?= $showForm?'':'none' ?>;margin-bottom:20px">
    <div class="card p5">
      <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:16px"><?= h(t('create_group')) ?></div>
      <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:12px"><?= h($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:12px">✓ <?= h($success) ?></div><?php endif; ?>
      <form method="POST" style="display:flex;flex-direction:column;gap:14px">

        <!-- Название + уровень HSK -->
        <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end">
          <div>
            <label class="label"><?= h(t('group_name')) ?> *</label>
            <input type="text" name="group_name" required class="input"
                   placeholder="<?= getLang()==='ru'?'Например: Группа A, Начинающие':(getLang()==='kz'?'Мысалы: А тобы':'E.g. Group A') ?>"
                   value="<?= h($_POST['group_name']??'') ?>">
          </div>
          <div>
            <label class="label"><?= h(t('hsk_level_label')) ?></label>
            <select name="hsk_level" class="input" style="width:auto">
              <option value="0"><?= h(t('hsk_none')) ?></option>
              <?php for ($i=1;$i<=6;$i++): ?>
                <option value="<?= $i ?>" <?= (($_POST['hsk_level']??0)==$i)?'selected':'' ?>>HSK <?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <!-- Дни недели -->
        <div>
          <label class="label"><?= h(t('days_of_week')) ?></label>
          <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px">
            <?php
            $selDays = $_POST['schedule'] ?? [];
            foreach ($days as $d):
            ?>
            <div>
              <input type="checkbox" name="schedule[]" value="<?= $d ?>" id="d_<?= $d ?>" class="day-check"
                     <?= in_array($d,$selDays)?'checked':'' ?>>
              <label for="d_<?= $d ?>" class="day-label"><?= $d ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Время занятий -->
        <div>
          <label class="label"><?= h(t('lesson_time_label')) ?></label>
          <div style="display:flex;align-items:center;gap:10px">
            <input type="time" name="time_from" class="input" style="width:130px" value="<?= h($_POST['time_from']??'') ?>">
            <span style="color:var(--muted);font-size:14px">—</span>
            <input type="time" name="time_to" class="input" style="width:130px" value="<?= h($_POST['time_to']??'') ?>">
          </div>
        </div>

        <div style="display:flex;gap:8px">
          <button type="submit" name="create_group" class="btn btn-black"><?= h(t('btn_create')) ?></button>
          <button type="button" onclick="document.getElementById('cf').style.display='none'" class="btn btn-ghost">
            <?= h(t('cancel')) ?>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Список групп -->
  <?php if (empty($groups)): ?>
    <div class="card p6" style="text-align:center;padding:56px 24px">
      <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#D4D4D8" stroke-width="1.5" style="margin:0 auto 14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
      <p style="color:var(--muted);font-size:14px"><?= h(t('no_groups_teacher')) ?></p>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px">
      <?php foreach ($groups as $g): ?>
      <a href="/teacher/group.php?id=<?= $g['id'] ?>" class="card card-link p5">

        <!-- Заголовок карточки -->
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:12px">
          <div style="font-size:15px;font-weight:700;color:var(--black)"><?= h($g['name']) ?></div>
          <?php if ($g['hsk_level']): ?>
            <span style="background:var(--black);color:#fff;border-radius:6px;padding:3px 8px;font-size:11px;font-weight:700;white-space:nowrap;flex-shrink:0">
              HSK <?= (int)$g['hsk_level'] ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Расписание и время -->
        <?php if ($g['schedule'] || $g['lesson_time']): ?>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:5px;margin-bottom:12px">
          <?php if ($g['schedule']): ?>
            <?php foreach (explode(',', $g['schedule']) as $d): ?>
              <span class="day-pill"><?= h($d) ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if ($g['lesson_time']): ?>
            <span style="font-size:12px;font-weight:600;color:var(--muted);margin-left:2px">
              <?= h($g['lesson_time']) ?>
            </span>
          <?php endif; ?>
        </div>
        <?php else: ?><div style="margin-bottom:12px"></div><?php endif; ?>

        <!-- Статы -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">
          <div class="stat" style="background:var(--bg-sub)"><div class="stat-value"><?= (int)$g['sc'] ?></div><div class="stat-label"><?= h(t('students_count')) ?></div></div>
          <div class="stat" style="background:var(--bg-sub)"><div class="stat-value"><?= (int)$g['lc'] ?></div><div class="stat-label"><?= h(t('lessons_count')) ?></div></div>
        </div>

        <!-- Инвайт-код -->
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

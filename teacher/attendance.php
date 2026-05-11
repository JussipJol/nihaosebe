<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /teacher/attendance.php?id='.((int)$_GET['id'])); exit; }
$user = requireAuth('teacher');
$gid = (int)($_GET['id']??0);
if (!$gid) { header('Location: /teacher/'); exit; }

$st = db()->prepare('SELECT * FROM `groups` WHERE id=? AND teacher_id=?');
$st->execute([$gid, $user['id']]);
$group = $st->fetch();
if (!$group) { header('Location: /teacher/'); exit; }

$st = db()->prepare('SELECT * FROM lessons WHERE group_id=? ORDER BY lesson_date ASC');
$st->execute([$gid]);
$lessons = $st->fetchAll();

$st = db()->prepare('SELECT u.id, u.name FROM student_groups sg JOIN users u ON u.id=sg.student_id WHERE sg.group_id=? ORDER BY u.name');
$st->execute([$gid]);
$students = $st->fetchAll();

$attMap = [];
if (!empty($lessons) && !empty($students)) {
    $lids = array_column($lessons, 'id');
    $placeholders = implode(',', array_fill(0, count($lids), '?'));
    $st = db()->prepare("SELECT student_id, lesson_id, status FROM attendance WHERE lesson_id IN ($placeholders)");
    $st->execute($lids);
    foreach ($st->fetchAll() as $row) {
        $attMap[$row['student_id']][$row['lesson_id']] = $row['status'];
    }
}

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="attendance_'.date('Y-m-d').'.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
    $header = ['Студент'];
    foreach ($lessons as $l) $header[] = date('d.m.Y', strtotime($l['lesson_date'])).' '.$l['title'];
    $header[] = '%'; $header[] = 'Присут.'; $header[] = 'Опоздал'; $header[] = 'Отсутст.';
    fputcsv($out, $header, ';');
    foreach ($students as $s) {
        $pr=$lt=$ab=0;
        $row = [$s['name']];
        foreach ($lessons as $l) {
            $st = $attMap[$s['id']][$l['id']] ?? null;
            if ($st==='present'){$row[]='П';$pr++;}
            elseif ($st==='late'){$row[]='О';$lt++;}
            elseif ($st==='absent'){$row[]='Н';$ab++;}
            else $row[]='-';
        }
        $total=count($lessons); $pct=$total>0?round(($pr+$lt)/$total*100):0;
        $row[]=$pct.'%'; $row[]=$pr; $row[]=$lt; $row[]=$ab;
        fputcsv($out, $row, ';');
    }
    fclose($out); exit;
}

// Per-student totals
$totals = [];
foreach ($students as $s) {
    $present = $late = $absent = 0;
    foreach ($lessons as $l) {
        $st = $attMap[$s['id']][$l['id']] ?? null;
        if ($st === 'present') $present++;
        elseif ($st === 'late') $late++;
        elseif ($st === 'absent') $absent++;
    }
    $total = count($lessons);
    $totals[$s['id']] = ['present'=>$present,'late'=>$late,'absent'=>$absent,'pct'=>$total>0?round(($present+$late)/$total*100):0];
}
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('attendance_overview')) ?> — <?= h($group['name']) ?></title>
<?= cssVars() ?>
<style>
.att-table { border-collapse: collapse; white-space: nowrap; }
.att-table th, .att-table td { padding: 8px 10px; border: 1px solid var(--border); font-size: 13px; }
.att-table th { background: var(--bg-sub); font-weight: 600; color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
.att-table td.name-cell { font-weight: 600; color: var(--black); background: #fff; position: sticky; left: 0; z-index: 1; border-right: 2px solid var(--border); }
.att-table th.name-th { position: sticky; left: 0; z-index: 2; background: var(--bg-sub); border-right: 2px solid var(--border); }
.dot { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; font-size: 10px; font-weight: 700; }
.dot-green { background: var(--green-bg); color: var(--green); }
.dot-red   { background: var(--red-bg);   color: var(--red);   }
.dot-amber { background: var(--amber-bg); color: var(--amber); }
.dot-none  { background: var(--bg-sub);   color: #D4D4D8;      }
.pct-cell { font-weight: 700; font-size: 13px; }
</style>
</head>
<body>
<?= sidebar($user, '/teacher/', 'id='.$gid) ?>
<div class="main">
<div class="content">

  <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);margin-bottom:18px;flex-wrap:wrap">
    <a href="/teacher/" style="color:var(--muted);text-decoration:none;font-weight:500"><?= h(t('teacher_dashboard')) ?></a>
    <span>›</span>
    <a href="/teacher/group.php?id=<?= $gid ?>" style="color:var(--muted);text-decoration:none;font-weight:500"><?= h($group['name']) ?></a>
    <span>›</span>
    <span style="color:var(--black);font-weight:600"><?= h(t('attendance_overview')) ?></span>
  </div>

  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:24px;flex-wrap:wrap">
    <div>
      <h1 class="page-title"><?= h(t('attendance_overview')) ?></h1>
      <p class="page-sub"><?= h($group['name']) ?> · <?= count($students) ?> студентов · <?= count($lessons) ?> уроков</p>
    </div>
    <?php if (!empty($lessons) && !empty($students)): ?>
    <a href="?id=<?= $gid ?>&export=csv" class="btn btn-outline btn-sm">
      <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      <?= h(t('export_csv')) ?>
    </a>
    <?php endif; ?>
  </div>

  <?php if (empty($lessons) || empty($students)): ?>
    <div class="card p5" style="text-align:center;color:var(--muted)"><?= empty($lessons)?h(t('no_lessons')):h(t('no_students')) ?></div>
  <?php else: ?>
    <div class="card" style="overflow:hidden">
      <div style="overflow-x:auto">
        <table class="att-table">
          <thead>
            <tr>
              <th class="name-th" style="min-width:160px">Студент</th>
              <?php foreach ($lessons as $l): ?>
                <th style="text-align:center;min-width:70px">
                  <div><?= date('d.m', strtotime($l['lesson_date'])) ?></div>
                  <div style="font-size:10px;color:var(--muted);font-weight:400;max-width:80px;overflow:hidden;text-overflow:ellipsis" title="<?= h($l['title']) ?>"><?= h(mb_strimwidth($l['title'],0,12,'…')) ?></div>
                </th>
              <?php endforeach; ?>
              <th style="text-align:center;min-width:60px">%</th>
              <th style="text-align:center;min-width:50px" title="Присутствовал">✓</th>
              <th style="text-align:center;min-width:50px" title="Опоздал">⏱</th>
              <th style="text-align:center;min-width:50px" title="Отсутствовал">✗</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $s):
              $t = $totals[$s['id']];
              $pc = $t['pct']>=80?'var(--green)':($t['pct']>=60?'var(--amber)':'var(--red)');
            ?>
            <tr>
              <td class="name-cell">
                <div style="display:flex;align-items:center;gap:7px">
                  <div class="avatar-sm" style="width:22px;height:22px;font-size:9px"><?= mb_strtoupper(mb_substr($s['name'],0,1)) ?></div>
                  <?= h($s['name']) ?>
                </div>
              </td>
              <?php foreach ($lessons as $l):
                $st = $attMap[$s['id']][$l['id']] ?? null;
                if ($st==='present') { $cls='dot-green'; $sym='✓'; }
                elseif ($st==='late') { $cls='dot-amber'; $sym='⏱'; }
                elseif ($st==='absent') { $cls='dot-red'; $sym='✗'; }
                else { $cls='dot-none'; $sym='–'; }
              ?>
              <td style="text-align:center"><span class="dot <?= $cls ?>"><?= $sym ?></span></td>
              <?php endforeach; ?>
              <td class="pct-cell" style="text-align:center;color:<?= $pc ?>"><?= $t['pct'] ?>%</td>
              <td style="text-align:center;color:var(--green);font-weight:600"><?= $t['present'] ?></td>
              <td style="text-align:center;color:var(--amber);font-weight:600"><?= $t['late'] ?></td>
              <td style="text-align:center;color:var(--red);font-weight:600"><?= $t['absent'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <!-- Legend -->
      <div style="padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:16px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted)"><span class="dot dot-green" style="width:18px;height:18px;font-size:9px">✓</span> Присутствовал</div>
        <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted)"><span class="dot dot-amber" style="width:18px;height:18px;font-size:9px">⏱</span> Опоздал</div>
        <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted)"><span class="dot dot-red" style="width:18px;height:18px;font-size:9px">✗</span> Отсутствовал</div>
        <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted)"><span class="dot dot-none" style="width:18px;height:18px;font-size:9px">–</span> Нет данных</div>
      </div>
    </div>
  <?php endif; ?>

</div>
</div>
</body>
</html>

<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /teacher/lesson.php?id='.((int)$_GET['id'])); exit; }
$user = requireAuth('teacher');
$lid = (int)($_GET['id']??0);
if (!$lid) { header('Location: /teacher/'); exit; }

$st = db()->prepare("SELECT l.*,g.id AS gid,g.name AS gn,g.subject FROM lessons l JOIN `groups` g ON g.id=l.group_id WHERE l.id=? AND g.teacher_id=?");
$st->execute([$lid,$user['id']]);
$lesson = $st->fetch();
if (!$lesson) { header('Location: /teacher/'); exit; }

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db=db(); $db->beginTransaction();
    try {
        foreach ($_POST['attendance']??[] as $sid=>$s) {
            if (!in_array($s,['present','absent','late'])) continue;
            $db->prepare('INSERT INTO attendance (lesson_id,student_id,status) VALUES (?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status)')->execute([$lid,(int)$sid,$s]);
        }
        $hwt=trim($_POST['hw_title']??'');
        if ($hwt) {
            $hwd=trim($_POST['hw_desc']??'')?:null; $hwdue=$_POST['hw_due']??''?:null;
            $ex=$db->prepare('SELECT id FROM homework WHERE lesson_id=?'); $ex->execute([$lid]); $ex=$ex->fetch();
            if ($ex) $db->prepare('UPDATE homework SET title=?,description=?,due_date=? WHERE id=?')->execute([$hwt,$hwd,$hwdue,$ex['id']]);
            else $db->prepare('INSERT INTO homework (lesson_id,title,description,due_date) VALUES (?,?,?,?)')->execute([$lid,$hwt,$hwd,$hwdue]);
        }
        foreach ($_POST['comment']??[] as $sid=>$c) {
            $c=trim($c); if (!$c) continue;
            $db->prepare('INSERT INTO comments (lesson_id,student_id,teacher_id,content) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE content=VALUES(content)')->execute([$lid,(int)$sid,$user['id'],$c]);
        }
        $db->commit(); $success=t('saved_ok');
    } catch (Exception $e) { $db->rollBack(); }
}

$st=db()->prepare("SELECT u.id,u.name,a.status AS att,c.content AS cm FROM student_groups sg JOIN users u ON u.id=sg.student_id LEFT JOIN attendance a ON a.lesson_id=? AND a.student_id=u.id LEFT JOIN comments c ON c.lesson_id=? AND c.student_id=u.id WHERE sg.group_id=? ORDER BY u.name");
$st->execute([$lid,$lid,$lesson['gid']]);
$students=$st->fetchAll();

$st=db()->prepare('SELECT * FROM homework WHERE lesson_id=?'); $st->execute([$lid]); $hw=$st->fetch();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($lesson['title']) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/teacher/', 'id='.$lid) ?>
<div class="main">
<div class="content" style="max-width:800px">

  <!-- Breadcrumb -->
  <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);margin-bottom:18px;flex-wrap:wrap">
    <a href="/teacher/" style="color:var(--muted);text-decoration:none;font-weight:500;transition:color .12s" onmouseover="this.style.color='var(--black)'" onmouseout="this.style.color='var(--muted)'"><?= h(t('teacher_dashboard')) ?></a>
    <span>›</span>
    <a href="/teacher/group.php?id=<?= $lesson['gid'] ?>" style="color:var(--muted);text-decoration:none;font-weight:500;transition:color .12s" onmouseover="this.style.color='var(--black)'" onmouseout="this.style.color='var(--muted)'"><?= h($lesson['gn']) ?></a>
    <span>›</span>
    <span style="color:var(--black);font-weight:600"><?= h($lesson['title']) ?></span>
  </div>

  <div style="margin-bottom:24px">
    <h1 class="page-title"><?= h($lesson['title']) ?></h1>
    <p class="page-sub"><?= date('d.m.Y',strtotime($lesson['lesson_date'])) ?><?= $lesson['subject']?' · '.h($lesson['subject']):'' ?></p>
  </div>

  <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:18px">✓ <?= h($success) ?></div><?php endif; ?>

  <form method="POST" style="display:flex;flex-direction:column;gap:14px">

    <!-- Attendance -->
    <div class="card p5">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <div class="icon-wrap"><svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <span style="font-size:15px;font-weight:700;color:var(--black)"><?= h(t('mark_attendance')) ?></span>
      </div>
      <?php if (empty($students)): ?>
        <p style="color:var(--muted);font-size:14px"><?= h(t('no_students')) ?></p>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:6px">
          <?php foreach ($students as $s): $att=$s['att']??'present'; ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--bg-sub);border:1px solid var(--border);border-radius:8px">
            <div style="display:flex;align-items:center;gap:9px">
              <div class="avatar-sm"><?= mb_strtoupper(mb_substr($s['name'],0,1)) ?></div>
              <span style="font-size:14px;font-weight:600;color:var(--black)"><?= h($s['name']) ?></span>
            </div>
            <div style="display:flex;gap:5px">
              <?php foreach (['present'=>[t('present'),'badge-green'],'late'=>[t('late'),'badge-amber'],'absent'=>[t('absent'),'badge-red']] as $v=>[$lbl,$bc]): ?>
              <label style="cursor:pointer">
                <input type="radio" name="attendance[<?= $s['id'] ?>]" value="<?= $v ?>" id="r<?= $s['id'].$v ?>" <?= $att===$v?'checked':'' ?> style="display:none">
                <span class="badge <?= $bc ?>" style="cursor:pointer;opacity:<?= $att===$v?'1':'.25' ?>;transition:opacity .12s" onclick="pickAtt(<?= $s['id'] ?>,'<?= $v ?>')"><?= h($lbl) ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Homework -->
    <div class="card p5">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <div class="icon-wrap"><svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        <span style="font-size:15px;font-weight:700;color:var(--black)"><?= h(t('add_homework')) ?></span>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px">
        <div>
          <label class="label"><?= h(t('hw_title')) ?></label>
          <input type="text" name="hw_title" class="input" value="<?= h($hw['title']??'') ?>" placeholder="Стр. 45, упр. 3">
        </div>
        <div>
          <label class="label"><?= h(t('hw_description')) ?></label>
          <textarea name="hw_desc" class="textarea" rows="2"><?= h($hw['description']??'') ?></textarea>
        </div>
        <div style="max-width:220px">
          <label class="label"><?= h(t('hw_due')) ?></label>
          <input type="date" name="hw_due" class="input" value="<?= h($hw['due_date']??'') ?>">
        </div>
      </div>
    </div>

    <!-- Comments -->
    <?php if (!empty($students)): ?>
    <div class="card p5">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <div class="icon-wrap"><svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></div>
        <span style="font-size:15px;font-weight:700;color:var(--black)"><?= h(t('add_comments')) ?></span>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach ($students as $s): ?>
        <div style="background:var(--bg-sub);border:1px solid var(--border);border-radius:8px;padding:12px">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
            <div class="avatar-sm" style="width:24px;height:24px;font-size:10px"><?= mb_strtoupper(mb_substr($s['name'],0,1)) ?></div>
            <span style="font-size:13px;font-weight:600;color:var(--black)"><?= h($s['name']) ?></span>
          </div>
          <textarea name="comment[<?= $s['id'] ?>]" class="textarea" rows="2" placeholder="<?= h(t('comment_placeholder')) ?>"><?= h($s['cm']??'') ?></textarea>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:8px">
      <button type="submit" class="btn btn-black btn-lg"><?= h(t('btn_save')) ?></button>
      <a href="/teacher/group.php?id=<?= $lesson['gid'] ?>" class="btn btn-outline"><?= h(t('back_to_group')) ?></a>
    </div>
  </form>

</div>
</div>
<script>
function pickAtt(id,val){
  ['present','late','absent'].forEach(function(v){
    var r=document.getElementById('r'+id+v);
    if(r){r.checked=v===val;r.nextElementSibling.style.opacity=v===val?'1':'.25';}
  });
}
</script>
</body>
</html>

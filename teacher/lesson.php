<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/lang.php';
require_once dirname(__DIR__) . '/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /teacher/lesson.php?id='.((int)$_GET['id'])); exit; }
$user = requireAuth('teacher');

$lessonId = (int)($_GET['id'] ?? 0);
if (!$lessonId) { header('Location: /teacher/'); exit; }

$st = db()->prepare("SELECT l.*,g.id AS gid,g.name AS gname,g.subject FROM lessons l JOIN `groups` g ON g.id=l.group_id WHERE l.id=? AND g.teacher_id=?");
$st->execute([$lessonId,$user['id']]);
$lesson = $st->fetch();
if (!$lesson) { header('Location: /teacher/'); exit; }

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = db(); $db->beginTransaction();
    try {
        foreach (($_POST['attendance']??[]) as $sid=>$status) {
            if (!in_array($status,['present','absent','late'])) continue;
            $db->prepare('INSERT INTO attendance (lesson_id,student_id,status) VALUES (?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status)')->execute([$lessonId,(int)$sid,$status]);
        }
        $hwTitle = trim($_POST['hw_title']??'');
        if ($hwTitle) {
            $hwDesc = trim($_POST['hw_desc']??'')?:null; $hwDue=$_POST['hw_due']??''?:null;
            $ex=$db->prepare('SELECT id FROM homework WHERE lesson_id=?'); $ex->execute([$lessonId]); $ex=$ex->fetch();
            if ($ex) $db->prepare('UPDATE homework SET title=?,description=?,due_date=? WHERE id=?')->execute([$hwTitle,$hwDesc,$hwDue,$ex['id']]);
            else $db->prepare('INSERT INTO homework (lesson_id,title,description,due_date) VALUES (?,?,?,?)')->execute([$lessonId,$hwTitle,$hwDesc,$hwDue]);
        }
        foreach (($_POST['comment']??[]) as $sid=>$content) {
            $content=trim($content); if (!$content) continue;
            $db->prepare('INSERT INTO comments (lesson_id,student_id,teacher_id,content) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE content=VALUES(content)')->execute([$lessonId,(int)$sid,$user['id'],$content]);
        }
        $db->commit(); $success=t('saved_ok');
    } catch (Exception $e) { $db->rollBack(); }
}

$st=db()->prepare("SELECT u.id,u.name,a.status AS att,c.content AS comment FROM student_groups sg JOIN users u ON u.id=sg.student_id LEFT JOIN attendance a ON a.lesson_id=? AND a.student_id=u.id LEFT JOIN comments c ON c.lesson_id=? AND c.student_id=u.id WHERE sg.group_id=? ORDER BY u.name");
$st->execute([$lessonId,$lessonId,$lesson['gid']]);
$students=$st->fetchAll();

$st=db()->prepare('SELECT * FROM homework WHERE lesson_id=?'); $st->execute([$lessonId]); $hw=$st->fetch();
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($lesson['title']) ?> — <?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<?= cssVars() ?>
</head>
<body>
<?= sidebar($user, '/teacher/', 'id='.$lessonId) ?>
<div class="main">
  <div style="max-width:840px">

    <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#7B6F5E;margin-bottom:20px;flex-wrap:wrap">
      <a href="/teacher/" style="color:#9A7A2A;font-weight:600;text-decoration:none"><?= h(t('teacher_dashboard')) ?></a>
      <span>›</span>
      <a href="/teacher/group.php?id=<?= $lesson['gid'] ?>" style="color:#9A7A2A;font-weight:600;text-decoration:none"><?= h($lesson['gname']) ?></a>
      <span>›</span>
      <span style="color:#1A1510;font-weight:700"><?= h($lesson['title']) ?></span>
    </div>

    <div style="margin-bottom:28px">
      <h1 class="page-title"><?= h($lesson['title']) ?></h1>
      <p class="page-sub"><?= date('d.m.Y',strtotime($lesson['lesson_date'])) ?><?= $lesson['subject']?' · '.h($lesson['subject']):'' ?></p>
    </div>

    <?php if ($success): ?><div class="alert-success" style="margin-bottom:20px">✓ <?= h($success) ?></div><?php endif; ?>

    <form method="POST" style="display:flex;flex-direction:column;gap:20px">

      <!-- Attendance -->
      <div class="card card-p">
        <h2 style="font-size:16px;font-weight:800;color:#1A1510;margin:0 0 16px;display:flex;align-items:center;gap:8px">
          <span style="width:28px;height:28px;background:#1A1510;border-radius:8px;display:inline-flex;align-items:center;justify-content:center">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </span>
          <?= h(t('mark_attendance')) ?>
        </h2>
        <?php if (empty($students)): ?>
          <p style="color:#7B6F5E;font-size:14px"><?= h(t('no_students')) ?></p>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:8px">
            <?php foreach ($students as $s): $att=$s['att']??'present'; ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#FAFAF8;border:1px solid #EDE5D4;border-radius:12px">
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:32px;height:32px;background:#1A1510;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:12px;color:#C9A84C;flex-shrink:0">
                  <?= mb_strtoupper(mb_substr($s['name'],0,1)) ?>
                </div>
                <span style="font-size:14px;font-weight:700;color:#1A1510"><?= h($s['name']) ?></span>
              </div>
              <div style="display:flex;gap:6px">
                <?php foreach (['present'=>['att-present',t('present')],'late'=>['att-late',t('late')],'absent'=>['att-absent',t('absent')]] as $val=>[$cls,$lbl]): ?>
                <label style="cursor:pointer">
                  <input type="radio" name="attendance[<?= $s['id'] ?>]" value="<?= $val ?>" <?= $att===$val?'checked':'' ?> style="display:none" id="r<?= $s['id'].$val ?>">
                  <span class="att-badge <?= $cls ?>" style="cursor:pointer;opacity:<?= $att===$val?'1':'.3' ?>;transition:opacity .15s" onclick="selectAtt(<?= $s['id'] ?>,'<?= $val ?>')"><?= h($lbl) ?></span>
                </label>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Homework -->
      <div class="card card-p">
        <h2 style="font-size:16px;font-weight:800;color:#1A1510;margin:0 0 16px;display:flex;align-items:center;gap:8px">
          <span style="width:28px;height:28px;background:#1A1510;border-radius:8px;display:inline-flex;align-items:center;justify-content:center">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </span>
          <?= h(t('add_homework')) ?>
        </h2>
        <div style="display:flex;flex-direction:column;gap:12px">
          <div>
            <label class="label"><?= h(t('hw_title')) ?></label>
            <input type="text" name="hw_title" class="input" value="<?= h($hw['title']??'') ?>" placeholder="<?= getLang()==='ru'?'Например: стр. 45, упр. 3':'E.g. page 45, ex. 3' ?>">
          </div>
          <div>
            <label class="label"><?= h(t('hw_description')) ?></label>
            <textarea name="hw_desc" class="textarea" rows="2"><?= h($hw['description']??'') ?></textarea>
          </div>
          <div style="max-width:240px">
            <label class="label"><?= h(t('hw_due')) ?></label>
            <input type="date" name="hw_due" class="input" value="<?= h($hw['due_date']??'') ?>">
          </div>
        </div>
      </div>

      <!-- Comments -->
      <?php if (!empty($students)): ?>
      <div class="card card-p">
        <h2 style="font-size:16px;font-weight:800;color:#1A1510;margin:0 0 16px;display:flex;align-items:center;gap:8px">
          <span style="width:28px;height:28px;background:#1A1510;border-radius:8px;display:inline-flex;align-items:center;justify-content:center">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
          </span>
          <?= h(t('add_comments')) ?>
        </h2>
        <div style="display:flex;flex-direction:column;gap:10px">
          <?php foreach ($students as $s): ?>
          <div style="background:#FAFAF8;border:1px solid #EDE5D4;border-radius:12px;padding:14px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
              <div style="width:26px;height:26px;background:#1A1510;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:10px;color:#C9A84C;flex-shrink:0">
                <?= mb_strtoupper(mb_substr($s['name'],0,1)) ?>
              </div>
              <span style="font-size:13px;font-weight:700;color:#1A1510"><?= h($s['name']) ?></span>
            </div>
            <textarea name="comment[<?= $s['id'] ?>]" class="textarea" rows="2" placeholder="<?= h(t('comment_placeholder')) ?>"><?= h($s['comment']??'') ?></textarea>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-dark" style="font-size:15px;padding:12px 28px"><?= h(t('btn_save')) ?></button>
        <a href="/teacher/group.php?id=<?= $lesson['gid'] ?>" class="btn btn-outline"><?= h(t('back_to_group')) ?></a>
      </div>
    </form>
  </div>
</div>

<script>
function selectAtt(id,val){
  ['present','late','absent'].forEach(function(v){
    var r=document.getElementById('r'+id+v);
    if(r){r.checked=(v===val); r.nextElementSibling.style.opacity=(v===val)?'1':'.3';}
  });
}
</script>
</body>
</html>

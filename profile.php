<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /profile.php'); exit; }
$user = requireLogin();

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cur  = $_POST['current_password'] ?? '';
    $new  = $_POST['new_password'] ?? '';
    $conf = $_POST['confirm_password'] ?? '';
    if (!$cur || !$new || !$conf) {
        $error = t('err_required');
    } elseif (strlen($new) < 6) {
        $error = t('err_min_password');
    } elseif ($new !== $conf) {
        $error = t('err_passwords');
    } elseif (!password_verify($cur, $user['password'])) {
        $error = t('err_wrong_password');
    } else {
        db()->prepare('UPDATE users SET password=? WHERE id=?')
            ->execute([password_hash($new, PASSWORD_BCRYPT), $user['id']]);
        $success = t('password_changed');
    }
}

$activeNav = '/profile.php';
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('settings')) ?> — <?= APP_NAME ?></title>
<?= cssVars() ?>
</head>
<body>
<?php if ($user['role'] !== 'admin'): ?>
<?= sidebar($user, '/profile.php') ?>
<div class="main">
<div class="content" style="max-width:480px">
<?php else: ?>
<div style="max-width:480px;margin:48px auto;padding:0 16px">
<?php endif; ?>

  <?php if ($user['role'] !== 'admin'): ?>
  <a href="<?= $user['role']==='teacher'?'/teacher/':'/student/' ?>" class="back-link">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    <?= h(t('back_to_dashboard')) ?>
  </a>
  <?php else: ?>
  <a href="/admin/" class="back-link" style="display:inline-flex;align-items:center;gap:5px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:18px">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Назад
  </a>
  <?php endif; ?>

  <div style="margin-bottom:24px">
    <h1 class="page-title"><?= h(t('settings')) ?></h1>
    <p class="page-sub"><?= h($user['name']) ?> · <span class="mono" style="font-size:12px"><?= h($user['login']) ?></span></p>
  </div>

  <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:16px"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:16px">✓ <?= h($success) ?></div><?php endif; ?>

  <div class="card p5">
    <div style="font-size:15px;font-weight:700;color:var(--black);margin-bottom:16px"><?= h(t('change_password')) ?></div>
    <form method="POST" style="display:flex;flex-direction:column;gap:12px">
      <div>
        <label class="label"><?= h(t('current_password')) ?></label>
        <input type="password" name="current_password" required class="input" autocomplete="current-password">
      </div>
      <div>
        <label class="label"><?= h(t('new_password_label')) ?></label>
        <input type="password" name="new_password" required class="input" autocomplete="new-password">
      </div>
      <div>
        <label class="label"><?= h(t('confirm_new_password')) ?></label>
        <input type="password" name="confirm_password" required class="input" autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-black" style="align-self:flex-start"><?= h(t('btn_save')) ?></button>
    </form>
  </div>

<?php if ($user['role'] !== 'admin'): ?>
</div>
</div>
<?php else: ?>
</div>
<?php endif; ?>
</body>
</html>

<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /login.php'); exit; }
if (isLoggedIn()) { $u = currentUser(); header('Location: '.($u['role']==='teacher'?'/teacher/':'/student/')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$email || !$pass) { $error = t('err_required'); }
    else {
        $user = loginUser($email, $pass);
        if ($user) { header('Location: '.($user['role']==='teacher'?'/teacher/':'/student/')); exit; }
        $error = t('err_credentials');
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('login_title')) ?> — <?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
*{font-family:'Segoe UI',system-ui,sans-serif;box-sizing:border-box}
body{margin:0;background:#FAFAF8;color:#1A1510;min-height:100vh;display:flex;flex-direction:column}
.input{width:100%;padding:11px 14px;border:1.5px solid #EDE5D4;border-radius:10px;font-size:14px;color:#1A1510;background:#fff;outline:none;transition:border-color .15s}
.input:focus{border-color:#C9A84C}
.btn-dark{background:#1A1510;color:#C9A84C;padding:12px 20px;border-radius:10px;font-weight:800;font-size:15px;border:none;cursor:pointer;width:100%;transition:opacity .15s}
.btn-dark:hover{opacity:.85}
</style>
</head>
<body>

<!-- Topbar -->
<nav style="background:#fff;border-bottom:1px solid #EDE5D4;padding:0 24px;height:56px;display:flex;align-items:center;justify-content:space-between">
  <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none">
    <svg width="32" height="32" viewBox="0 0 100 100" fill="none">
      <circle cx="50" cy="50" r="46" stroke="#1A1510" stroke-width="2.5"/>
      <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#1A1510"/>
      <circle cx="46" cy="30" r="2.5" fill="#C0392B"/>
    </svg>
    <span style="font-weight:900;font-size:13px;letter-spacing:.1em;color:#1A1510">НУ, НИХАУ СЕБЕ!</span>
  </a>
  <div style="display:flex;gap:4px;background:#F5F0E8;border-radius:50px;padding:4px">
    <?php foreach (['ru','kz','en'] as $l): ?>
      <a href="?lang=<?= $l ?>" style="padding:5px 12px;border-radius:50px;font-size:12px;font-weight:700;text-decoration:none;<?= getLang()===$l ? 'background:#1A1510;color:#C9A84C' : 'color:#7B6F5E' ?>">
        <?= t("lang_{$l}") ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>

<!-- Form -->
<div style="flex:1;display:flex;align-items:center;justify-content:center;padding:40px 16px">
  <div style="width:100%;max-width:400px">
    <div style="background:#fff;border:1px solid #EDE5D4;border-radius:20px;padding:36px">

      <div style="text-align:center;margin-bottom:28px">
        <svg width="52" height="52" viewBox="0 0 100 100" fill="none" style="margin:0 auto 14px">
          <circle cx="50" cy="50" r="46" stroke="#1A1510" stroke-width="2.5"/>
          <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#1A1510"/>
          <circle cx="46" cy="30" r="2.5" fill="#C0392B"/>
        </svg>
        <h1 style="font-size:22px;font-weight:900;color:#1A1510;margin:0"><?= h(t('login_title')) ?></h1>
      </div>

      <?php if ($error): ?>
        <div style="background:#FFF5F5;border:1px solid #FFCCCC;color:#C0392B;padding:11px 14px;border-radius:10px;font-size:14px;margin-bottom:18px"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="POST" style="display:flex;flex-direction:column;gap:16px">
        <div>
          <label style="display:block;font-size:13px;font-weight:700;color:#1A1510;margin-bottom:6px"><?= h(t('field_email')) ?></label>
          <input type="email" name="email" required autocomplete="email" class="input"
                 value="<?= h($_POST['email'] ?? '') ?>" placeholder="you@example.com">
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:700;color:#1A1510;margin-bottom:6px"><?= h(t('field_password')) ?></label>
          <input type="password" name="password" required autocomplete="current-password" class="input">
        </div>
        <button type="submit" class="btn-dark" style="margin-top:4px"><?= h(t('btn_login')) ?></button>
      </form>

      <p style="text-align:center;font-size:13px;color:#7B6F5E;margin-top:20px;margin-bottom:0">
        <?= h(t('no_account')) ?>
        <a href="/register.php" style="color:#9A7A2A;font-weight:700;text-decoration:none"><?= h(t('nav_register')) ?></a>
      </p>
    </div>
  </div>
</div>
</body>
</html>

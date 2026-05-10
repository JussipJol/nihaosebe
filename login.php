<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /login.php'); exit; }
if (isLoggedIn()) { $u = currentUser(); header('Location: '.($u['role']==='teacher'?'/teacher/':'/student/')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$login || !$pass) { $error = t('err_required'); }
    else {
        $user = loginUser($login, $pass);
        if ($user) { redirectByRole($user); }
        $error = t('err_credentials');
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('login_title')) ?> — <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--red:#E11D48;--black:#09090B;--muted:#71717A;--border:#E4E4E7}
body{font-family:"Inter",system-ui,sans-serif;background:#FAFAFA;color:var(--black);min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
.topnav{background:#fff;border-bottom:1px solid var(--border);padding:0 24px;height:54px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.logo{display:flex;align-items:center;gap:9px}
.logo-name{font-size:13px;font-weight:700;color:var(--black);letter-spacing:.02em}
.lang-g{display:flex;gap:2px;background:#F4F4F5;border-radius:7px;padding:3px;border:1px solid var(--border)}
.lang-o{padding:4px 10px;border-radius:5px;font-size:12px;font-weight:600;color:var(--muted);transition:all .12s}
.lang-o.on{background:#fff;color:var(--black);box-shadow:0 1px 3px rgba(0,0,0,.08)}
.page{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 16px}
.box{background:#fff;border:1px solid var(--border);border-radius:12px;padding:32px;width:100%;max-width:380px}
.box-logo{display:flex;flex-direction:column;align-items:center;gap:10px;margin-bottom:24px}
.box-title{font-size:20px;font-weight:800;color:var(--black);letter-spacing:-.02em}
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
.label{font-size:12px;font-weight:500;color:var(--black)}
.input{width:100%;padding:9px 11px;border:1.5px solid var(--border);border-radius:7px;font-size:14px;color:var(--black);background:#fff;outline:none;transition:border-color .12s;font-family:inherit}
.input:focus{border-color:var(--red)}
.input::placeholder{color:#A1A1AA}
.btn-submit{width:100%;padding:10px;border-radius:8px;background:var(--black);color:#fff;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:inherit;margin-top:4px;transition:background .12s}
.btn-submit:hover{background:#18181B}
.alert-err{background:#FFF1F2;border:1px solid #FECDD3;color:var(--red);padding:10px 13px;border-radius:7px;font-size:13px;font-weight:500;margin-bottom:16px}
.footer-link{text-align:center;margin-top:18px;font-size:13px;color:var(--muted)}
.footer-link a{color:var(--red);font-weight:600}
</style>
</head>
<body>
<nav class="topnav">
  <a href="/" class="logo">
    <svg width="28" height="28" viewBox="0 0 100 100" fill="none">
      <circle cx="50" cy="50" r="46" stroke="#09090B" stroke-width="2.5"/>
      <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#09090B"/>
      <circle cx="46" cy="30" r="2.5" fill="#E11D48"/>
    </svg>
    <span class="logo-name">НУ, НИХАУ СЕБЕ!</span>
  </a>
  <div class="lang-g">
    <?php foreach(['ru','kz','en'] as $l): ?>
      <a href="?lang=<?= $l ?>" class="lang-o<?= getLang()===$l?' on':'' ?>"><?= t("lang_{$l}") ?></a>
    <?php endforeach; ?>
  </div>
</nav>

<div class="page">
  <div class="box">
    <div class="box-logo">
      <svg width="44" height="44" viewBox="0 0 100 100" fill="none">
        <circle cx="50" cy="50" r="46" stroke="#09090B" stroke-width="2.5"/>
        <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#09090B"/>
        <circle cx="46" cy="30" r="2.5" fill="#E11D48"/>
      </svg>
      <div class="box-title"><?= h(t('login_title')) ?></div>
    </div>

    <?php if ($error): ?><div class="alert-err"><?= h($error) ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="label">Логин</label>
        <input type="text" name="login" required autocomplete="username" class="input"
               value="<?= h($_POST['login'] ?? '') ?>" placeholder="Ваш логин">
      </div>
      <div class="form-group">
        <label class="label"><?= h(t('field_password')) ?></label>
        <input type="password" name="password" required autocomplete="current-password" class="input">
      </div>
      <button type="submit" class="btn-submit"><?= h(t('btn_login')) ?></button>
    </form>

    <div class="footer-link">
      <?= h(t('no_account')) ?> <a href="/register.php"><?= h(t('nav_register')) ?></a>
    </div>
  </div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /register.php'); exit; }
if (isLoggedIn()) { $u = currentUser(); header('Location: '.($u['role']==='teacher'?'/teacher/':'/student/')); exit; }

$preRole = in_array($_GET['role'] ?? '', ['student','teacher']) ? $_GET['role'] : '';
$step    = $preRole ? 2 : (int)($_POST['step'] ?? 1);
$role    = $_POST['role'] ?? $preRole;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        if (!in_array($role, ['student','teacher'])) { $error = t('err_required'); $step = 1; }
        else { $step = 2; }
    } elseif ($step === 2) {
        $name=$trim_name=trim($_POST['name']??'');
        $email=trim($_POST['email']??'');
        $pass=$_POST['password']??'';
        $pass2=$_POST['password2']??'';
        if (!$name||!$email||!$pass||!$pass2) { $error=t('err_required'); }
        elseif (strlen($pass)<6) { $error=t('err_min_password'); }
        elseif ($pass!==$pass2) { $error=t('err_passwords'); }
        else {
            $id=registerUser($name,$email,$pass,$role);
            if ($id===null) { $error=t('err_email_exists'); }
            else { session_regenerate_id(true); $_SESSION['user_id']=$id; header('Location: '.($role==='teacher'?'/teacher/':'/student/')); exit; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('register_title')) ?> — <?= APP_NAME ?></title>
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
.box{background:#fff;border:1px solid var(--border);border-radius:12px;padding:32px;width:100%;max-width:420px}
.box-title{font-size:20px;font-weight:800;color:var(--black);letter-spacing:-.02em;margin-bottom:4px}
.box-sub{font-size:13px;color:var(--muted);margin-bottom:22px}
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
.role-card{display:flex;align-items:flex-start;gap:12px;padding:14px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer;transition:border-color .12s;margin-bottom:10px}
.role-card:hover{border-color:#D4D4D8}
.role-card input[type=radio]{accent-color:var(--red);margin-top:2px;flex-shrink:0}
.role-card-title{font-size:14px;font-weight:600;color:var(--black);margin-bottom:2px}
.role-card-desc{font-size:12px;color:var(--muted);line-height:1.4}
.back-btn{display:inline-flex;align-items:center;gap:5px;background:none;border:none;cursor:pointer;font-size:13px;color:var(--muted);font-family:inherit;padding:0;margin-bottom:16px;transition:color .12s}
.back-btn:hover{color:var(--black)}
.role-tag{display:inline-block;background:#FFF1F2;color:var(--red);font-size:11px;font-weight:600;padding:2px 8px;border-radius:4px;margin-bottom:16px;text-transform:uppercase;letter-spacing:.05em}
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

<?php if ($step === 1): ?>
  <div class="box-title"><?= h(t('choose_role')) ?></div>
  <div class="box-sub">НУ, НИХАУ СЕБЕ!</div>

  <?php if ($error): ?><div class="alert-err"><?= h($error) ?></div><?php endif; ?>

  <form method="POST">
    <input type="hidden" name="step" value="1">
    <label class="role-card">
      <input type="radio" name="role" value="student" <?= $role==='student'?'checked':'' ?>>
      <div>
        <div class="role-card-title"><?= h(t('role_student')) ?></div>
        <div class="role-card-desc"><?= h(t('role_student_desc')) ?></div>
      </div>
    </label>
    <label class="role-card">
      <input type="radio" name="role" value="teacher" <?= $role==='teacher'?'checked':'' ?>>
      <div>
        <div class="role-card-title"><?= h(t('role_teacher')) ?></div>
        <div class="role-card-desc"><?= h(t('role_teacher_desc')) ?></div>
      </div>
    </label>
    <button type="submit" class="btn-submit"><?= h(t('btn_next')) ?> →</button>
  </form>

<?php else: ?>
  <form method="POST" style="margin-bottom:0">
    <input type="hidden" name="step" value="1">
    <input type="hidden" name="role" value="<?= h($role) ?>">
    <button type="submit" class="back-btn">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      <?= h(t('btn_back')) ?>
    </button>
  </form>

  <div class="box-title"><?= h(t('register_title')) ?></div>
  <div class="role-tag"><?= $role==='teacher' ? h(t('role_teacher')) : h(t('role_student')) ?></div>

  <?php if ($error): ?><div class="alert-err"><?= h($error) ?></div><?php endif; ?>

  <form method="POST">
    <input type="hidden" name="step" value="2">
    <input type="hidden" name="role" value="<?= h($role) ?>">
    <div class="form-group">
      <label class="label"><?= h(t('field_name')) ?></label>
      <input type="text" name="name" required autocomplete="name" class="input" value="<?= h($_POST['name']??'') ?>" placeholder="Имя Фамилия">
    </div>
    <div class="form-group">
      <label class="label"><?= h(t('field_email')) ?></label>
      <input type="email" name="email" required autocomplete="email" class="input" value="<?= h($_POST['email']??'') ?>" placeholder="you@example.com">
    </div>
    <div class="form-group">
      <label class="label"><?= h(t('field_password')) ?></label>
      <input type="password" name="password" required autocomplete="new-password" minlength="6" class="input">
    </div>
    <div class="form-group">
      <label class="label"><?= h(t('field_password_confirm')) ?></label>
      <input type="password" name="password2" required autocomplete="new-password" minlength="6" class="input">
    </div>
    <button type="submit" class="btn-submit"><?= h(t('btn_register')) ?></button>
  </form>
<?php endif; ?>

  <div class="footer-link">
    <?= h(t('has_account')) ?> <a href="/login.php"><?= h(t('nav_login')) ?></a>
  </div>
</div>
</div>
</body>
</html>

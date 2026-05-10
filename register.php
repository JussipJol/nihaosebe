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
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if (!$name || !$email || !$pass || !$pass2) { $error = t('err_required'); }
        elseif (strlen($pass) < 6) { $error = t('err_min_password'); }
        elseif ($pass !== $pass2)  { $error = t('err_passwords'); }
        else {
            $id = registerUser($name, $email, $pass, $role);
            if ($id === null) { $error = t('err_email_exists'); }
            else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $id;
                header('Location: '.($role==='teacher'?'/teacher/':'/student/'));
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('register_title')) ?> — <?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
*{font-family:'Segoe UI',system-ui,sans-serif;box-sizing:border-box}
body{margin:0;background:#FAFAF8;color:#1A1510;min-height:100vh;display:flex;flex-direction:column}
.input{width:100%;padding:11px 14px;border:1.5px solid #EDE5D4;border-radius:10px;font-size:14px;color:#1A1510;background:#fff;outline:none;transition:border-color .15s}
.input:focus{border-color:#C9A84C}
.btn-dark{background:#1A1510;color:#C9A84C;padding:12px 20px;border-radius:10px;font-weight:800;font-size:15px;border:none;cursor:pointer;width:100%;transition:opacity .15s}
.btn-dark:hover{opacity:.85}
.role-card{display:flex;align-items:center;gap:16px;padding:18px;border:2px solid #EDE5D4;border-radius:14px;cursor:pointer;transition:border-color .15s;margin-bottom:10px}
.role-card:hover{border-color:#C9A84C}
.role-card input[type=radio]{accent-color:#1A1510;width:18px;height:18px;flex-shrink:0}
</style>
</head>
<body>

<nav style="background:#fff;border-bottom:1px solid #EDE5D4;padding:0 24px;height:56px;display:flex;align-items:center;justify-content:space-between">
  <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none">
    <svg width="32" height="32" viewBox="0 0 100 100" fill="none">
      <circle cx="50" cy="50" r="46" stroke="#1A1510" stroke-width="2.5"/>
      <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#1A1510"/>
      <circle cx="46" cy="30" r="2.5" fill="#C0392B"/>
    </svg>
    <span style="font-weight:900;font-size:13px;letter-spacing:.1em;color:#1A1510;display:none" class="sm:block">НУ, НИХАУ СЕБЕ!</span>
  </a>
  <div style="display:flex;gap:4px;background:#F5F0E8;border-radius:50px;padding:4px">
    <?php foreach (['ru','kz','en'] as $l): ?>
      <a href="?lang=<?= $l ?>" style="padding:5px 12px;border-radius:50px;font-size:12px;font-weight:700;text-decoration:none;<?= getLang()===$l ? 'background:#1A1510;color:#C9A84C' : 'color:#7B6F5E' ?>"><?= t("lang_{$l}") ?></a>
    <?php endforeach; ?>
  </div>
</nav>

<div style="flex:1;display:flex;align-items:center;justify-content:center;padding:40px 16px">
<div style="width:100%;max-width:420px">

<?php if ($step === 1): ?>
  <div style="background:#fff;border:1px solid #EDE5D4;border-radius:20px;padding:36px">
    <h1 style="font-size:22px;font-weight:900;color:#1A1510;text-align:center;margin:0 0 4px"><?= h(t('choose_role')) ?></h1>
    <p style="text-align:center;font-size:13px;color:#7B6F5E;margin:0 0 24px">НУ, НИХАУ СЕБЕ!</p>

    <?php if ($error): ?>
      <div style="background:#FFF5F5;border:1px solid #FFCCCC;color:#C0392B;padding:11px 14px;border-radius:10px;font-size:14px;margin-bottom:16px"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="step" value="1">

      <label class="role-card">
        <input type="radio" name="role" value="student" <?= $role==='student'?'checked':'' ?>>
        <div>
          <div style="font-weight:800;color:#1A1510;margin-bottom:3px;display:flex;align-items:center;gap:6px">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <?= h(t('role_student')) ?>
          </div>
          <div style="font-size:13px;color:#7B6F5E"><?= h(t('role_student_desc')) ?></div>
        </div>
      </label>

      <label class="role-card">
        <input type="radio" name="role" value="teacher" <?= $role==='teacher'?'checked':'' ?>>
        <div>
          <div style="font-weight:800;color:#1A1510;margin-bottom:3px;display:flex;align-items:center;gap:6px">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <?= h(t('role_teacher')) ?>
          </div>
          <div style="font-size:13px;color:#7B6F5E"><?= h(t('role_teacher_desc')) ?></div>
        </div>
      </label>

      <button type="submit" class="btn-dark" style="margin-top:8px"><?= h(t('btn_next')) ?> →</button>
    </form>

    <p style="text-align:center;font-size:13px;color:#7B6F5E;margin-top:18px">
      <?= h(t('has_account')) ?>
      <a href="/login.php" style="color:#9A7A2A;font-weight:700;text-decoration:none"><?= h(t('nav_login')) ?></a>
    </p>
  </div>

<?php else: ?>
  <div style="background:#fff;border:1px solid #EDE5D4;border-radius:20px;padding:36px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
      <form method="POST" style="margin:0">
        <input type="hidden" name="step" value="1">
        <input type="hidden" name="role" value="<?= h($role) ?>">
        <button type="submit" style="background:none;border:none;cursor:pointer;color:#7B6F5E;padding:4px;display:flex">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
      </form>
      <div>
        <h1 style="font-size:22px;font-weight:900;color:#1A1510;margin:0 0 2px"><?= h(t('register_title')) ?></h1>
        <p style="font-size:12px;font-weight:700;color:#C9A84C;margin:0"><?= $role==='teacher' ? h(t('role_teacher')) : h(t('role_student')) ?></p>
      </div>
    </div>

    <?php if ($error): ?>
      <div style="background:#FFF5F5;border:1px solid #FFCCCC;color:#C0392B;padding:11px 14px;border-radius:10px;font-size:14px;margin-bottom:16px"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" style="display:flex;flex-direction:column;gap:14px">
      <input type="hidden" name="step" value="2">
      <input type="hidden" name="role" value="<?= h($role) ?>">

      <div>
        <label style="display:block;font-size:13px;font-weight:700;color:#1A1510;margin-bottom:6px"><?= h(t('field_name')) ?></label>
        <input type="text" name="name" required autocomplete="name" class="input"
               value="<?= h($_POST['name'] ?? '') ?>" placeholder="Имя Фамилия">
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:700;color:#1A1510;margin-bottom:6px"><?= h(t('field_email')) ?></label>
        <input type="email" name="email" required autocomplete="email" class="input"
               value="<?= h($_POST['email'] ?? '') ?>" placeholder="you@example.com">
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:700;color:#1A1510;margin-bottom:6px"><?= h(t('field_password')) ?></label>
        <input type="password" name="password" required autocomplete="new-password" minlength="6" class="input">
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:700;color:#1A1510;margin-bottom:6px"><?= h(t('field_password_confirm')) ?></label>
        <input type="password" name="password2" required autocomplete="new-password" minlength="6" class="input">
      </div>
      <button type="submit" class="btn-dark" style="margin-top:4px"><?= h(t('btn_register')) ?></button>
    </form>

    <p style="text-align:center;font-size:13px;color:#7B6F5E;margin-top:18px">
      <?= h(t('has_account')) ?>
      <a href="/login.php" style="color:#9A7A2A;font-weight:700;text-decoration:none"><?= h(t('nav_login')) ?></a>
    </p>
  </div>
<?php endif; ?>

</div>
</div>
</body>
</html>

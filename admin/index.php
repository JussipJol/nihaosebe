<?php
require_once dirname(__DIR__).'/includes/config.php';
require_once dirname(__DIR__).'/includes/db.php';
require_once dirname(__DIR__).'/includes/auth.php';
require_once dirname(__DIR__).'/includes/lang.php';
require_once dirname(__DIR__).'/includes/layout.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /admin/'); exit; }
$admin = requireAdmin();

$error = $success = '';

// Создать пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = trim($_POST['password'] ?? '');
        $role  = $_POST['role'] ?? '';
        if (!$name || !$email || !$pass || !in_array($role, ['student','teacher'])) {
            $error = 'Заполните все поля.';
        } elseif (strlen($pass) < 6) {
            $error = 'Пароль минимум 6 символов.';
        } else {
            $id = createUser($name, $email, $pass, $role);
            if ($id === null) $error = 'Этот email уже зарегистрирован.';
            else $success = "Профиль создан: {$email} / {$pass}";
        }
    } elseif ($_POST['action'] === 'delete') {
        $uid = (int)($_POST['uid'] ?? 0);
        if ($uid && $uid !== $admin['id']) {
            db()->prepare('DELETE FROM users WHERE id = ? AND role != ?')->execute([$uid, 'admin']);
            $success = 'Пользователь удалён.';
        }
    } elseif ($_POST['action'] === 'reset_pass') {
        $uid  = (int)($_POST['uid'] ?? 0);
        $pass = trim($_POST['new_password'] ?? '');
        if ($uid && strlen($pass) >= 6) {
            db()->prepare('UPDATE users SET password = ? WHERE id = ?')
                ->execute([password_hash($pass, PASSWORD_BCRYPT), $uid]);
            $success = 'Пароль изменён.';
        } else {
            $error = 'Пароль минимум 6 символов.';
        }
    }
}

// Фильтр
$filterRole = $_GET['role'] ?? '';
$search     = trim($_GET['q'] ?? '');

$where = ["role != 'admin'"];
$params = [];
if ($filterRole && in_array($filterRole, ['student','teacher'])) {
    $where[] = 'role = ?'; $params[] = $filterRole;
}
if ($search) {
    $where[] = '(name LIKE ? OR email LIKE ?)';
    $params[] = "%{$search}%"; $params[] = "%{$search}%";
}

$sql = 'SELECT * FROM users WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC';
$st  = db()->prepare($sql);
$st->execute($params);
$users = $st->fetchAll();

// Статистика
$stats = db()->query("SELECT role, COUNT(*) AS cnt FROM users WHERE role != 'admin' GROUP BY role")->fetchAll();
$statMap = ['student' => 0, 'teacher' => 0];
foreach ($stats as $s) $statMap[$s['role']] = $s['cnt'];
$groupCount = (int)db()->query("SELECT COUNT(*) FROM `groups`")->fetchColumn();

$showForm = $error || ($success && isset($_POST['action']) && $_POST['action'] === 'create');
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Администратор — <?= APP_NAME ?></title>
<?= cssVars() ?>
<style>
.users-table { width: 100%; border-collapse: collapse; }
.users-table th { text-align: left; padding: 10px 14px; font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; border-bottom: 1px solid var(--border); white-space: nowrap; }
.users-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: 14px; color: var(--black); vertical-align: middle; }
.users-table tr:last-child td { border-bottom: none; }
.users-table tr:hover td { background: var(--bg-sub); }
.actions { display: flex; gap: 6px; align-items: center; }
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 12px; padding: 24px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
.modal-title { font-size: 16px; font-weight: 700; color: var(--black); margin-bottom: 16px; }
.pass-gen { display: flex; gap: 6px; }
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <a href="/" class="sb-brand">
    <svg width="28" height="28" viewBox="0 0 100 100" fill="none">
      <circle cx="50" cy="50" r="46" stroke="white" stroke-width="2" opacity=".2"/>
      <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="white"/>
      <circle cx="46" cy="30" r="2.5" fill="var(--red)"/>
    </svg>
    <div>
      <div class="sb-brand-name"><?= APP_NAME ?></div>
      <div class="sb-brand-sub">Администратор</div>
    </div>
  </a>
  <nav class="sb-nav">
    <a href="/admin/" class="nav-link active">
      <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      Пользователи
    </a>
  </nav>
  <div class="sb-bottom">
    <div class="lang-row">
      <?php foreach(['ru','kz','en'] as $l): ?>
        <a href="?lang=<?= $l ?>" class="lang-btn<?= getLang()===$l?' on':'' ?>"><?= t("lang_{$l}") ?></a>
      <?php endforeach; ?>
    </div>
    <div class="sb-user">
      <div class="sb-avatar"><?= mb_strtoupper(mb_substr($admin['name'],0,1)) ?></div>
      <div style="min-width:0">
        <div class="sb-uname"><?= h($admin['name']) ?></div>
        <div class="sb-urole">Администратор</div>
      </div>
    </div>
    <a href="/logout.php" class="sb-logout">
      <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Выйти
    </a>
  </div>
</aside>

<div class="main">
<div class="content">

  <!-- Заголовок -->
  <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:24px">
    <div>
      <h1 class="page-title">Пользователи</h1>
      <p class="page-sub">Управление аккаунтами студентов и преподавателей</p>
    </div>
    <button onclick="document.getElementById('createModal').classList.add('open')" class="btn btn-black">
      <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Создать профиль
    </button>
  </div>

  <!-- Статистика -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:24px">
    <div class="card p5">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Студентов</div>
      <div style="font-size:28px;font-weight:800;color:var(--black)"><?= $statMap['student'] ?></div>
    </div>
    <div class="card p5">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Преподавателей</div>
      <div style="font-size:28px;font-weight:800;color:var(--black)"><?= $statMap['teacher'] ?></div>
    </div>
    <div class="card p5">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Групп</div>
      <div style="font-size:28px;font-weight:800;color:var(--black)"><?= $groupCount ?></div>
    </div>
  </div>

  <!-- Алерты -->
  <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:14px"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:14px">✓ <?= h($success) ?></div><?php endif; ?>

  <!-- Фильтр / поиск -->
  <div class="card" style="margin-bottom:12px">
    <form method="GET" style="display:flex;gap:8px;padding:14px;flex-wrap:wrap;align-items:center">
      <input type="text" name="q" value="<?= h($search) ?>" placeholder="Поиск по имени или email..." class="input" style="flex:1;min-width:200px">
      <select name="role" class="input" style="width:auto">
        <option value="">Все роли</option>
        <option value="student" <?= $filterRole==='student'?'selected':'' ?>>Студенты</option>
        <option value="teacher" <?= $filterRole==='teacher'?'selected':'' ?>>Преподаватели</option>
      </select>
      <button type="submit" class="btn btn-outline">Найти</button>
      <?php if ($search || $filterRole): ?>
        <a href="/admin/" class="btn btn-ghost">Сбросить</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Таблица -->
  <div class="card" style="overflow:hidden">
    <?php if (empty($users)): ?>
      <div style="padding:40px;text-align:center;color:var(--muted);font-size:14px">Пользователи не найдены.</div>
    <?php else: ?>
      <div style="overflow-x:auto">
        <table class="users-table">
          <thead>
            <tr>
              <th>Имя</th>
              <th>Email</th>
              <th>Роль</th>
              <th>Зарегистрирован</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:9px">
                  <div class="avatar-sm"><?= mb_strtoupper(mb_substr($u['name'],0,1)) ?></div>
                  <span style="font-weight:600"><?= h($u['name']) ?></span>
                </div>
              </td>
              <td style="color:var(--muted)"><?= h($u['email']) ?></td>
              <td>
                <?php if ($u['role']==='teacher'): ?>
                  <span class="badge badge-amber">Преподаватель</span>
                <?php else: ?>
                  <span class="badge badge-green">Студент</span>
                <?php endif; ?>
              </td>
              <td style="color:var(--muted);font-size:13px"><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
              <td>
                <div class="actions">
                  <button onclick="openReset(<?= $u['id'] ?>, '<?= h($u['name']) ?>')" class="btn btn-outline btn-sm">Сменить пароль</button>
                  <form method="POST" onsubmit="return confirm('Удалить пользователя <?= h($u['name']) ?>?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
</div>

<!-- Modal: Создать профиль -->
<div class="modal-overlay <?= $showForm ? 'open' : '' ?>" id="createModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title">Создать профиль</div>
    <?php if ($error && isset($_POST['action']) && $_POST['action']==='create'): ?>
      <div class="alert alert-error" style="margin-bottom:12px"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="POST" style="display:flex;flex-direction:column;gap:12px">
      <input type="hidden" name="action" value="create">
      <div>
        <label class="label">Имя и фамилия</label>
        <input type="text" name="name" required class="input" value="<?= h($_POST['name']??'') ?>" placeholder="Имя Фамилия">
      </div>
      <div>
        <label class="label">Email</label>
        <input type="email" name="email" required class="input" value="<?= h($_POST['email']??'') ?>" placeholder="user@example.com">
      </div>
      <div>
        <label class="label">Роль</label>
        <select name="role" class="input">
          <option value="student" <?= ($_POST['role']??'')==='student'?'selected':'' ?>>Студент</option>
          <option value="teacher" <?= ($_POST['role']??'')==='teacher'?'selected':'' ?>>Преподаватель</option>
        </select>
      </div>
      <div>
        <label class="label">Пароль</label>
        <div class="pass-gen">
          <input type="text" name="password" id="newPass" required class="input mono" value="<?= h($_POST['password']??'') ?>" placeholder="Минимум 6 символов">
          <button type="button" onclick="genPass()" class="btn btn-outline" style="flex-shrink:0">Сгенерировать</button>
        </div>
        <p style="font-size:11px;color:var(--muted);margin-top:4px">Запомните и передайте пользователю — пароль хранится в зашифрованном виде</p>
      </div>
      <div style="display:flex;gap:8px;margin-top:4px">
        <button type="submit" class="btn btn-black">Создать</button>
        <button type="button" onclick="document.getElementById('createModal').classList.remove('open')" class="btn btn-ghost">Отмена</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Сменить пароль -->
<div class="modal-overlay" id="resetModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title">Сменить пароль — <span id="resetName"></span></div>
    <?php if ($error && isset($_POST['action']) && $_POST['action']==='reset_pass'): ?>
      <div class="alert alert-error" style="margin-bottom:12px"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="POST" style="display:flex;flex-direction:column;gap:12px">
      <input type="hidden" name="action" value="reset_pass">
      <input type="hidden" name="uid" id="resetUid">
      <div>
        <label class="label">Новый пароль</label>
        <div class="pass-gen">
          <input type="text" name="new_password" id="resetPass" required class="input mono" placeholder="Минимум 6 символов">
          <button type="button" onclick="genPassReset()" class="btn btn-outline" style="flex-shrink:0">Сгенерировать</button>
        </div>
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-black">Сохранить</button>
        <button type="button" onclick="document.getElementById('resetModal').classList.remove('open')" class="btn btn-ghost">Отмена</button>
      </div>
    </form>
  </div>
</div>

<script>
function genPass() {
    var chars = 'abcdefghjkmnpqrstuvwxyz23456789';
    var pass = '';
    for (var i = 0; i < 8; i++) pass += chars[Math.floor(Math.random() * chars.length)];
    document.getElementById('newPass').value = pass;
}
function genPassReset() {
    var chars = 'abcdefghjkmnpqrstuvwxyz23456789';
    var pass = '';
    for (var i = 0; i < 8; i++) pass += chars[Math.floor(Math.random() * chars.length)];
    document.getElementById('resetPass').value = pass;
}
function openReset(uid, name) {
    document.getElementById('resetUid').value = uid;
    document.getElementById('resetName').textContent = name;
    document.getElementById('resetModal').classList.add('open');
}
</script>
</body>
</html>

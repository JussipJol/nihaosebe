<?php
require_once __DIR__ . '/db.php';

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array
{
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $st = db()->prepare('SELECT * FROM users WHERE id = ?');
        $st->execute([$_SESSION['user_id']]);
        $user = $st->fetch() ?: null;
    }
    return $user;
}

function redirectByRole(array $user): void
{
    match($user['role']) {
        'admin'   => header('Location: /admin/'),
        'teacher' => header('Location: /teacher/'),
        default   => header('Location: /student/'),
    };
    exit;
}

function requireAuth(string $role): array
{
    if (!isLoggedIn()) { header('Location: /login.php'); exit; }
    $user = currentUser();
    if ($user['role'] !== $role) { redirectByRole($user); }
    return $user;
}

function requireAdmin(): array
{
    if (!isLoggedIn()) { header('Location: /login.php'); exit; }
    $user = currentUser();
    if ($user['role'] !== 'admin') { redirectByRole($user); }
    return $user;
}

function loginUser(string $email, string $password): ?array
{
    $st = db()->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([strtolower(trim($email))]);
    $user = $st->fetch();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        return $user;
    }
    return null;
}

function createUser(string $name, string $email, string $password, string $role): ?int
{
    try {
        $st = db()->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $st->execute([trim($name), strtolower(trim($email)), password_hash($password, PASSWORD_BCRYPT), $role]);
        return (int) db()->lastInsertId();
    } catch (PDOException $e) {
        return null;
    }
}

function generateInviteCode(): string
{
    do {
        $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $st   = db()->prepare('SELECT id FROM `groups` WHERE invite_code = ?');
        $st->execute([$code]);
    } while ($st->fetch());
    return $code;
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

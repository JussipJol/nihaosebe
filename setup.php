<?php
/**
 * Первоначальная настройка — запустите один раз, затем удалите этот файл.
 * Открыть в браузере: http://nihao.test/setup.php
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// Добавляем admin в ENUM если ещё нет
try {
    db()->exec("ALTER TABLE users MODIFY role ENUM('student','teacher','admin') NOT NULL");
} catch (PDOException $e) {
    // уже обновлено — игнорируем
}

$st = db()->prepare("SELECT id FROM users WHERE role = 'admin'");
$st->execute();
$exists = $st->fetch();

if ($exists) {
    echo '<p style="font-family:monospace;padding:24px;color:#666">
    Администратор уже создан. Удалите этот файл.<br><br>
    <a href="/login.php">→ Войти</a>
    </p>';
    exit;
}

$email    = 'admin@nunihaosebe.kz';
$password = 'admin123'; // ← смените после первого входа

db()->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')")
    ->execute(['Администратор', $email, password_hash($password, PASSWORD_BCRYPT)]);

echo '<div style="font-family:monospace;padding:32px;max-width:480px">
<h2 style="margin-bottom:16px">✓ Администратор создан</h2>
<table style="border-collapse:collapse;width:100%">
  <tr><td style="padding:6px 12px 6px 0;color:#666">Email:</td><td style="font-weight:bold">' . $email . '</td></tr>
  <tr><td style="padding:6px 12px 6px 0;color:#666">Пароль:</td><td style="font-weight:bold">' . $password . '</td></tr>
</table>
<p style="margin-top:16px;color:#c00"><strong>⚠ Удалите файл setup.php после входа!</strong></p>
<p style="margin-top:16px"><a href="/login.php" style="background:#09090B;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold">→ Войти</a></p>
</div>';

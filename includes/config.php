<?php
// ============================================================
// НАСТРОЙКИ БАЗЫ ДАННЫХ
// Замените значения на данные из панели Plesk:
//   Plesk → Базы данных → Добавить базу данных
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME',  'nunihaosebe');        // имя БД из Plesk
define('DB_USER',  'root');   // пользователь БД
define('DB_PASS',  ''); // пароль
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// НАСТРОЙКИ ПРИЛОЖЕНИЯ
// ============================================================
define('APP_NAME', 'nunihaosebe');
define('SESSION_LIFETIME', 86400); // 24 часа

// Запуск сессии (вызывается один раз)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

<?php
require_once __DIR__ . '/config.php';

function setLang(string $lang): void
{
    $allowed = ['ru', 'kz', 'en'];
    $_SESSION['lang'] = in_array($lang, $allowed, true) ? $lang : 'ru';
}

function getLang(): string
{
    return $_SESSION['lang'] ?? 'ru';
}

function t(string $key): string
{
    static $tr = null;
    if ($tr === null) {
        $lang = getLang();
        $file = __DIR__ . "/lang/{$lang}.php";
        $tr   = file_exists($file) ? require $file : require __DIR__ . '/lang/ru.php';
    }
    return $tr[$key] ?? $key;
}

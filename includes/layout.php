<?php
function cssVars(): string { return '
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --red:      #E11D48;
  --red-bg:   #FFF1F2;
  --red-bd:   #FECDD3;
  --black:    #09090B;
  --muted:    #71717A;
  --border:   #E4E4E7;
  --bg:       #FFFFFF;
  --bg-sub:   #FAFAFA;
  --green:    #16A34A;
  --green-bg: #F0FDF4;
  --amber:    #D97706;
  --amber-bg: #FFFBEB;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: "Inter", system-ui, sans-serif; background: var(--bg); color: var(--black); font-size: 14px; line-height: 1.5; -webkit-font-smoothing: antialiased; }

/* ── Sidebar ── */
.sidebar { position: fixed; top: 0; bottom: 0; left: 0; width: 220px; background: var(--black); display: flex; flex-direction: column; z-index: 40; overflow-y: auto; }
.sb-brand { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,.06); display: flex; align-items: center; gap: 10px; text-decoration: none; }
.sb-brand-name { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: .02em; line-height: 1.2; }
.sb-brand-sub { font-size: 10px; color: var(--red); font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
.sb-nav { flex: 1; padding: 10px; display: flex; flex-direction: column; gap: 1px; }
.nav-link { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: 7px; font-size: 13px; font-weight: 500; color: rgba(255,255,255,.45); text-decoration: none; transition: background .12s, color .12s; }
.nav-link svg { opacity: .5; transition: opacity .12s; }
.nav-link:hover { background: rgba(255,255,255,.06); color: rgba(255,255,255,.8); }
.nav-link:hover svg { opacity: .7; }
.nav-link.active { background: rgba(225,29,72,.12); color: var(--red); font-weight: 600; }
.nav-link.active svg { opacity: 1; }
.sb-bottom { padding: 10px; border-top: 1px solid rgba(255,255,255,.06); display: flex; flex-direction: column; gap: 6px; }
.lang-row { display: flex; gap: 3px; background: rgba(255,255,255,.05); border-radius: 7px; padding: 3px; }
.lang-btn { flex: 1; text-align: center; padding: 5px; border-radius: 5px; font-size: 11px; font-weight: 600; color: rgba(255,255,255,.3); text-decoration: none; transition: all .12s; }
.lang-btn.on { background: var(--red); color: #fff; }
.sb-user { display: flex; align-items: center; gap: 9px; padding: 6px 2px; }
.sb-avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--red); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.sb-uname { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.75); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-urole { font-size: 10px; color: rgba(255,255,255,.25); }
.sb-logout { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 7px; font-size: 12px; font-weight: 500; color: rgba(255,255,255,.25); text-decoration: none; transition: all .12s; }
.sb-logout:hover { background: rgba(255,255,255,.05); color: rgba(255,255,255,.55); }

/* ── Topbar (mobile) ── */
.topbar { display: none; position: fixed; top: 0; left: 0; right: 0; height: 52px; background: var(--black); z-index: 40; padding: 0 16px; align-items: center; justify-content: space-between; }
@media (max-width: 767px) { .sidebar { display: none; } .topbar { display: flex; } }

/* ── Layout ── */
.main { margin-left: 220px; }
.content { max-width: 960px; padding: 36px 40px; margin: 0 auto; }
@media (max-width: 767px) { .main { margin-left: 0; } .content { padding: 64px 16px 32px; } }

/* ── Cards ── */
.card { background: #fff; border: 1px solid var(--border); border-radius: 10px; }
.p4 { padding: 16px; }
.p5 { padding: 20px; }
.p6 { padding: 24px; }
.card-link { display: block; text-decoration: none; transition: border-color .12s, box-shadow .12s; }
.card-link:hover { border-color: #D4D4D8; box-shadow: 0 1px 6px rgba(0,0,0,.06); }

/* ── Buttons ── */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all .12s; text-decoration: none; white-space: nowrap; font-family: inherit; }
.btn-sm { padding: 6px 10px; font-size: 12px; }
.btn-lg { padding: 11px 22px; font-size: 14px; font-weight: 700; border-radius: 9px; }
.btn-black { background: var(--black); color: #fff; }
.btn-black:hover { background: #18181B; }
.btn-red { background: var(--red); color: #fff; }
.btn-red:hover { background: #BE123C; }
.btn-outline { background: #fff; color: var(--black); border: 1.5px solid var(--border); }
.btn-outline:hover { border-color: #A1A1AA; }
.btn-ghost { background: transparent; color: var(--muted); }
.btn-ghost:hover { background: var(--bg-sub); color: var(--black); }
.btn-danger { background: #fff; color: var(--red); border: 1.5px solid var(--red-bd); }
.btn-danger:hover { background: var(--red-bg); }

/* ── Forms ── */
.label { display: block; font-size: 12px; font-weight: 500; color: var(--black); margin-bottom: 5px; }
.input { width: 100%; padding: 8px 11px; border: 1.5px solid var(--border); border-radius: 7px; font-size: 14px; color: var(--black); background: #fff; outline: none; transition: border-color .12s; font-family: inherit; }
.input:focus { border-color: var(--red); }
.input::placeholder { color: #A1A1AA; }
.textarea { resize: none; width: 100%; padding: 8px 11px; border: 1.5px solid var(--border); border-radius: 7px; font-size: 14px; color: var(--black); background: #fff; outline: none; transition: border-color .12s; font-family: inherit; line-height: 1.5; }
.textarea:focus { border-color: var(--red); }

/* ── Alerts ── */
.alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; }
.alert-error { background: var(--red-bg); color: var(--red); border: 1px solid var(--red-bd); }
.alert-success { background: var(--green-bg); color: var(--green); border: 1px solid #BBF7D0; }

/* ── Badges ── */
.badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 600; }
.badge-green { background: var(--green-bg); color: var(--green); }
.badge-red { background: var(--red-bg); color: var(--red); }
.badge-amber { background: var(--amber-bg); color: var(--amber); }

/* ── Stats ── */
.stat { padding: 14px 16px; border-radius: 8px; }
.stat-value { font-size: 22px; font-weight: 800; color: var(--black); line-height: 1; letter-spacing: -.02em; }
.stat-label { font-size: 11px; font-weight: 500; color: var(--muted); margin-top: 3px; text-transform: uppercase; letter-spacing: .05em; }

/* ── Progress ── */
.progress { height: 3px; background: var(--border); border-radius: 999px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 999px; }
.progress-green { background: var(--green); }
.progress-red { background: var(--red); }
.progress-amber { background: var(--amber); }

/* ── Page title ── */
.page-title { font-size: 22px; font-weight: 800; color: var(--black); letter-spacing: -.025em; }
.page-sub { font-size: 13px; color: var(--muted); margin-top: 3px; }

/* ── Misc ── */
.avatar-sm { width: 28px; height: 28px; border-radius: 50%; background: var(--black); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.icon-wrap { width: 28px; height: 28px; border-radius: 7px; background: var(--black); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.back-link { display: inline-flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 500; color: var(--muted); text-decoration: none; margin-bottom: 18px; transition: color .12s; }
.back-link:hover { color: var(--black); }
.divider { border: none; border-top: 1px solid var(--border); }
.mono { font-family: ui-monospace, "SF Mono", Consolas, monospace; }
</style>';
}

function sidebar(array $user, string $active, string $langQ = ''): string
{
    $lang = getLang();
    $name = h($user['name']);
    $ini  = mb_strtoupper(mb_substr($user['name'], 0, 1));
    $role = $user['role'] === 'teacher' ? t('role_teacher') : t('role_student');
    $base = $user['role'] === 'teacher' ? '/teacher/' : '/student/';

    $settingsPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
    $navItems = $user['role'] === 'teacher'
        ? [
            [$base, '<path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>', t('teacher_dashboard')],
            ['/profile.php', $settingsPath, t('settings')],
          ]
        : [
            [$base, '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>', t('student_dashboard')],
            ['/student/homework.php', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', t('my_homework')],
            ['/profile.php', $settingsPath, t('settings')],
          ];

    $nav = '';
    foreach ($navItems as [$href, $path, $label]) {
        $cls = $href === $active ? ' active' : '';
        $nav .= "<a href=\"{$href}\" class=\"nav-link{$cls}\"><svg width=\"15\" height=\"15\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\" stroke-width=\"2\">{$path}</svg>{$label}</a>";
    }

    $sep = $langQ ? '&amp;'.$langQ : '';
    $langs = '';
    foreach (['ru','kz','en'] as $l) {
        $on = $lang===$l ? ' on' : '';
        $langs .= "<a href=\"?lang={$l}{$sep}\" class=\"lang-btn{$on}\">" . t("lang_{$l}") . "</a>";
    }

    $mLangs = implode('', array_map(fn($l) =>
        "<a href=\"?lang={$l}{$sep}\" style=\"font-size:12px;font-weight:600;text-decoration:none;color:" . ($lang===$l?'var(--red)':'rgba(255,255,255,.35)') . "\">" . t("lang_{$l}") . "</a>",
        ['ru','kz','en']));

    return "
<aside class=\"sidebar\">
  <a href=\"/\" class=\"sb-brand\">
    <svg width=\"28\" height=\"28\" viewBox=\"0 0 100 100\" fill=\"none\">
      <circle cx=\"50\" cy=\"50\" r=\"46\" stroke=\"white\" stroke-width=\"2\" opacity=\".2\"/>
      <path d=\"M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z\" fill=\"white\"/>
      <circle cx=\"46\" cy=\"30\" r=\"2.5\" fill=\"var(--red)\"/>
    </svg>
    <div>
      <div class=\"sb-brand-name\">".APP_NAME."</div>
      <div class=\"sb-brand-sub\">Образовательный центр</div>
    </div>
  </a>
  <nav class=\"sb-nav\">{$nav}</nav>
  <div class=\"sb-bottom\">
    <div class=\"lang-row\">{$langs}</div>
    <div class=\"sb-user\">
      <div class=\"sb-avatar\">{$ini}</div>
      <div style=\"min-width:0\">
        <div class=\"sb-uname\">{$name}</div>
        <div class=\"sb-urole\">{$role}</div>
      </div>
    </div>
    <a href=\"/logout.php\" class=\"sb-logout\">
      <svg width=\"13\" height=\"13\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\" stroke-width=\"2\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1\"/></svg>
      ".t('nav_logout')."
    </a>
  </div>
</aside>

<div class=\"topbar\">
  <a href=\"/\" style=\"display:flex;align-items:center;gap:8px;text-decoration:none\">
    <svg width=\"22\" height=\"22\" viewBox=\"0 0 100 100\" fill=\"none\"><circle cx=\"50\" cy=\"50\" r=\"46\" stroke=\"white\" stroke-width=\"2\" opacity=\".2\"/><path d=\"M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z\" fill=\"white\"/><circle cx=\"46\" cy=\"30\" r=\"2.5\" fill=\"var(--red)\"/></svg>
    <span style=\"font-size:13px;font-weight:700;color:#fff\">".APP_NAME."</span>
  </a>
  <div style=\"display:flex;align-items:center;gap:12px\">{$mLangs}
    <a href=\"/logout.php\" style=\"font-size:12px;font-weight:500;color:rgba(255,255,255,.3);text-decoration:none\">".t('nav_logout')."</a>
  </div>
</div>";
}

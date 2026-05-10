<?php
// Shared CSS + HTML head for all dashboard/auth pages
// Usage: include this AFTER setting $pageTitle

function cssVars(): string {
    return '
<style>
* { font-family: "Segoe UI", system-ui, sans-serif; box-sizing: border-box; }
body { margin: 0; background: #fff; color: #1A1510; }

/* ── Brand tokens ── */
:root {
  --ink:    #1A1510;
  --gold:   #C9A84C;
  --gold-d: #9A7A2A;
  --muted:  #7B6F5E;
  --border: #EDE5D4;
  --bg:     #fff;
  --bg-sub: #FAFAF8;
}

/* ── Sidebar ── */
.sidebar { width: 240px; background: #1A1510; color: #fff; display: flex; flex-direction: column; height: 100vh; position: fixed; top: 0; left: 0; z-index: 40; }
.sidebar-logo { padding: 20px; border-bottom: 1px solid #2D2519; display: flex; align-items: center; gap: 10px; }
.sidebar-logo-icon { width: 32px; height: 32px; background: #C9A84C; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 13px; color: #1A1510; flex-shrink: 0; }
.sidebar-nav { flex: 1; padding: 16px 12px; display: flex; flex-direction: column; gap: 4px; }
.nav-link { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; text-decoration: none; color: rgba(255,255,255,.55); font-size: 14px; font-weight: 600; transition: all .15s; }
.nav-link:hover { background: rgba(255,255,255,.07); color: #fff; }
.nav-link.active { background: #C9A84C; color: #1A1510; }
.sidebar-footer { padding: 16px 12px; border-top: 1px solid #2D2519; display: flex; flex-direction: column; gap: 8px; }
.lang-switcher { display: flex; gap: 4px; background: #2D2519; border-radius: 8px; padding: 4px; }
.lang-btn { flex: 1; text-align: center; padding: 6px 4px; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none; color: rgba(255,255,255,.4); transition: all .15s; }
.lang-btn.active { background: #C9A84C; color: #1A1510; }
.user-row { display: flex; align-items: center; gap: 10px; padding: 4px 2px; }
.avatar { width: 34px; height: 34px; background: #C9A84C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 13px; color: #1A1510; flex-shrink: 0; }
.logout-link { display: flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 10px; color: rgba(255,255,255,.4); font-size: 13px; font-weight: 600; text-decoration: none; transition: all .15s; }
.logout-link:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.8); }

/* ── Mobile topbar ── */
.topbar { display: none; position: fixed; top: 0; left: 0; right: 0; height: 56px; background: #1A1510; z-index: 40; padding: 0 16px; align-items: center; justify-content: space-between; }
@media (max-width: 767px) { .sidebar { display: none; } .topbar { display: flex; } }

/* ── Main content ── */
.main { margin-left: 240px; min-height: 100vh; padding: 36px 40px; }
@media (max-width: 767px) { .main { margin-left: 0; padding: 72px 16px 32px; } }

/* ── Cards ── */
.card { background: #fff; border: 1px solid #EDE5D4; border-radius: 16px; }
.card-p { padding: 24px; }
.card-hover:hover { border-color: #C9A84C; box-shadow: 0 4px 20px rgba(0,0,0,.06); }

/* ── Buttons ── */
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; text-decoration: none; border: none; transition: opacity .15s; }
.btn:hover { opacity: .85; }
.btn-dark { background: #1A1510; color: #C9A84C; }
.btn-gold { background: #C9A84C; color: #1A1510; }
.btn-outline { background: #fff; color: #1A1510; border: 1.5px solid #EDE5D4; }
.btn-outline:hover { border-color: #1A1510; opacity: 1; }
.btn-danger { background: #fff; color: #C0392B; border: 1.5px solid #FCC; }
.btn-danger:hover { background: #FFF5F5; opacity: 1; }

/* ── Inputs ── */
.input { width: 100%; padding: 10px 14px; border: 1.5px solid #EDE5D4; border-radius: 10px; font-size: 14px; color: #1A1510; background: #fff; outline: none; transition: border-color .15s; }
.input:focus { border-color: #C9A84C; }
.label { display: block; font-size: 13px; font-weight: 700; color: #1A1510; margin-bottom: 6px; }
.textarea { resize: none; width: 100%; padding: 10px 14px; border: 1.5px solid #EDE5D4; border-radius: 10px; font-size: 14px; color: #1A1510; background: #fff; outline: none; transition: border-color .15s; }
.textarea:focus { border-color: #C9A84C; }

/* ── Alerts ── */
.alert-error   { background: #FFF5F5; border: 1px solid #FFCCCC; color: #C0392B; padding: 12px 16px; border-radius: 10px; font-size: 14px; }
.alert-success { background: #F5FFF8; border: 1px solid #B8EFC8; color: #2D7A4F; padding: 12px 16px; border-radius: 10px; font-size: 14px; }

/* ── Stats badge ── */
.stat-box { border-radius: 12px; padding: 12px 16px; text-align: center; }
.stat-box .num { font-size: 22px; font-weight: 900; color: #1A1510; }
.stat-box .lbl { font-size: 11px; color: #7B6F5E; font-weight: 600; margin-top: 2px; }

/* ── Attendance badge ── */
.att-present { background: #EFFFF5; color: #2D7A4F; }
.att-absent  { background: #FFF5F5; color: #C0392B; }
.att-late    { background: #FFFBF0; color: #9A6800; }
.att-badge   { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }

/* ── Page title ── */
.page-title { font-size: 26px; font-weight: 900; color: #1A1510; }
.page-sub   { font-size: 14px; color: #7B6F5E; margin-top: 4px; }
</style>';
}

function sidebar(array $user, string $active, string $langBase = ''): string
{
    $lang = getLang();
    $name = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
    $initial = mb_strtoupper(mb_substr($user['name'], 0, 1));
    $role = $user['role'] === 'teacher' ? t('role_teacher') : t('role_student');
    $base = $user['role'] === 'teacher' ? '/teacher/' : '/student/';

    $navItems = $user['role'] === 'teacher'
        ? [[$base, 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', t('teacher_dashboard')]]
        : [[$base, 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', t('student_dashboard')]];

    $navHtml = '';
    foreach ($navItems as [$href, $icon, $label]) {
        $cls = $href === $active ? ' active' : '';
        $navHtml .= "<a href=\"{$href}\" class=\"nav-link{$cls}\"><svg width=\"16\" height=\"16\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\" stroke-width=\"2\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"{$icon}\"/></svg>{$label}</a>";
    }

    $langs = '';
    foreach (['ru','kz','en'] as $l) {
        $cls = $lang === $l ? ' active' : '';
        $sep = $langBase ? "&amp;{$langBase}" : '';
        $langs .= "<a href=\"?lang={$l}{$sep}\" class=\"lang-btn{$cls}\">" . t("lang_{$l}") . "</a>";
    }

    return "
    <aside class=\"sidebar\">
      <div class=\"sidebar-logo\">
        <div class=\"sidebar-logo-icon\">N</div>
        <span style=\"font-weight:900;font-size:13px;letter-spacing:.06em\">" . APP_NAME . "</span>
      </div>
      <nav class=\"sidebar-nav\">{$navHtml}</nav>
      <div class=\"sidebar-footer\">
        <div class=\"lang-switcher\">{$langs}</div>
        <div class=\"user-row\">
          <div class=\"avatar\">{$initial}</div>
          <div style=\"min-width:0\">
            <p style=\"font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis\">{$name}</p>
            <p style=\"font-size:11px;color:rgba(255,255,255,.4)\">{$role}</p>
          </div>
        </div>
        <a href=\"/logout.php\" class=\"logout-link\">
          <svg width=\"14\" height=\"14\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\" stroke-width=\"2\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1\"/></svg>
          " . t('nav_logout') . "
        </a>
      </div>
    </aside>

    <div class=\"topbar\">
      <div style=\"display:flex;align-items:center;gap:8px\">
        <div class=\"sidebar-logo-icon\">N</div>
        <span style=\"font-weight:900;font-size:13px;color:#fff\">" . APP_NAME . "</span>
      </div>
      <div style=\"display:flex;align-items:center;gap:12px\">
        " . implode('', array_map(fn($l) => "<a href=\"?lang={$l}{$langBase}\" style=\"font-size:12px;font-weight:700;text-decoration:none;color:" . ($lang===$l ? '#C9A84C' : 'rgba(255,255,255,.4)') . "\">" . t("lang_{$l}") . "</a>", ['ru','kz','en'])) . "
        <a href=\"/logout.php\" style=\"font-size:12px;color:rgba(255,255,255,.4);text-decoration:none\">" . t('nav_logout') . "</a>
      </div>
    </div>";
}

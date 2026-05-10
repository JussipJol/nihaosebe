<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /'); exit; }
if (isLoggedIn()) { $u = currentUser(); header('Location: '.($u['role']==='teacher'?'/teacher/':'/student/')); exit; }
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>НУ, НИХАУ СЕБЕ! — Образовательный центр</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--red:#E11D48;--black:#09090B;--muted:#71717A;--border:#E4E4E7;--bg:#fff;--bg-sub:#FAFAFA}
body{font-family:"Inter",system-ui,sans-serif;background:#fff;color:var(--black);font-size:15px;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}

/* NAV */
.nav{position:sticky;top:0;background:rgba(255,255,255,.9);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);z-index:50}
.nav-inner{max-width:1100px;margin:0 auto;padding:0 24px;height:58px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.nav-logo{display:flex;align-items:center;gap:10px}
.nav-logo-text{font-size:13px;font-weight:700;color:var(--black);letter-spacing:.02em}
.nav-logo-sub{font-size:10px;font-weight:600;color:var(--red);letter-spacing:.05em;text-transform:uppercase}
.nav-right{display:flex;align-items:center;gap:6px}
.lang-group{display:flex;gap:2px;background:var(--bg-sub);border-radius:7px;padding:3px;border:1px solid var(--border)}
.lang-opt{padding:4px 10px;border-radius:5px;font-size:12px;font-weight:600;color:var(--muted);transition:all .12s}
.lang-opt.on{background:#fff;color:var(--black);box-shadow:0 1px 3px rgba(0,0,0,.08)}
.btn-nav-login{padding:7px 14px;border-radius:7px;font-size:13px;font-weight:600;color:var(--black);transition:background .12s}
.btn-nav-login:hover{background:var(--bg-sub)}
.btn-nav-reg{padding:7px 14px;border-radius:7px;font-size:13px;font-weight:600;background:var(--black);color:#fff;transition:background .12s}
.btn-nav-reg:hover{background:#18181B}

/* HERO */
.hero{padding:88px 24px 80px;text-align:center;max-width:720px;margin:0 auto}
.hero-eyebrow{font-size:12px;font-weight:600;letter-spacing:.1em;color:var(--muted);text-transform:uppercase;margin-bottom:16px}
.hero-title{font-size:clamp(40px,7vw,72px);font-weight:900;color:var(--black);line-height:1;letter-spacing:-.035em;margin-bottom:18px}
.hero-title span{color:var(--red)}
.hero-sub{font-size:17px;color:var(--muted);line-height:1.6;max-width:500px;margin:0 auto 36px}
.hero-ctas{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
.btn-hero-primary{display:flex;align-items:center;gap:8px;background:var(--black);color:#fff;padding:13px 24px;border-radius:9px;font-size:14px;font-weight:700;transition:background .12s}
.btn-hero-primary:hover{background:#18181B}
.btn-hero-outline{display:flex;align-items:center;gap:8px;background:#fff;color:var(--black);padding:13px 24px;border-radius:9px;font-size:14px;font-weight:700;border:1.5px solid var(--border);transition:border-color .12s}
.btn-hero-outline:hover{border-color:#A1A1AA}

/* SECTIONS */
.section{padding:72px 24px}
.section-alt{background:var(--bg-sub);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.section-inner{max-width:1000px;margin:0 auto}
.section-header{text-align:center;margin-bottom:40px}
.section-eyebrow{font-size:12px;font-weight:600;letter-spacing:.1em;color:var(--red);text-transform:uppercase;margin-bottom:10px}
.section-title{font-size:28px;font-weight:800;color:var(--black);letter-spacing:-.02em}

/* CARDS */
.card{background:#fff;border:1px solid var(--border);border-radius:10px}

/* FEATURES */
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
@media(max-width:680px){.features-grid{grid-template-columns:1fr}}
.feature-card{padding:24px}
.feature-icon{width:40px;height:40px;background:var(--black);border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:16px}
.feature-title{font-size:15px;font-weight:700;color:var(--black);margin-bottom:6px}
.feature-desc{font-size:13px;color:var(--muted);line-height:1.6}

/* PRICING */
.tab-row{display:flex;gap:6px;justify-content:center;margin-bottom:28px}
.tab{padding:8px 22px;border-radius:7px;font-size:13px;font-weight:600;border:1.5px solid var(--border);color:var(--muted);background:#fff;cursor:pointer;transition:all .12s}
.tab.on{background:var(--black);color:#fff;border-color:var(--black)}
.pricing-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;max-width:680px;margin:0 auto}
@media(max-width:560px){.pricing-grid{grid-template-columns:1fr}}
.price-card{padding:22px}
.price-card-header{display:flex;align-items:center;gap:10px;margin-bottom:18px}
.price-card-icon{width:36px;height:36px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.price-card-title{font-size:13px;font-weight:700;color:var(--black);text-transform:uppercase;letter-spacing:.04em}
.price-card-sub{font-size:11px;color:var(--muted)}
.price-row{display:flex;align-items:center;gap:8px;padding:10px 0;border-bottom:1px dashed var(--border)}
.price-row:last-child{border-bottom:none;padding-bottom:0}
.price-num{background:var(--black);color:#fff;border-radius:5px;padding:3px 9px;font-size:13px;font-weight:700;flex-shrink:0}
.price-dots{flex:1;border-bottom:1px dashed var(--border);opacity:.3;margin:0 8px}
.price-amount{font-size:16px;font-weight:800;color:var(--red);white-space:nowrap}

/* HSK */
.hsk-card{max-width:480px;margin:0 auto;padding:24px}
.hsk-row{display:flex;align-items:center;gap:10px;padding:11px 0;border-bottom:1px dashed var(--border)}
.hsk-row:last-child{border-bottom:none;padding-bottom:0}
.hsk-badge{background:var(--black);color:#fff;border-radius:6px;padding:5px 10px;font-size:12px;font-weight:700;flex-shrink:0;min-width:52px;text-align:center}
.hsk-dots{flex:1;border-bottom:1px dashed var(--border);opacity:.3;margin:0 10px}

/* CONTACT */
.contact-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;max-width:800px;margin:0 auto}
@media(max-width:620px){.contact-grid{grid-template-columns:1fr}}
.contact-card{padding:24px;text-align:center}
.contact-icon{width:40px;height:40px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
.contact-label{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}

/* CTA BANNER */
.cta-banner{background:var(--black);padding:64px 24px;text-align:center}
.cta-title{font-size:32px;font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:8px}
.cta-sub{font-size:15px;color:rgba(255,255,255,.4);margin-bottom:32px}
.cta-btns{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
.btn-cta-red{background:var(--red);color:#fff;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:700;transition:background .12s}
.btn-cta-red:hover{background:#BE123C}
.btn-cta-ghost{border:1.5px solid rgba(255,255,255,.15);color:rgba(255,255,255,.6);padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;transition:all .12s}
.btn-cta-ghost:hover{border-color:rgba(255,255,255,.35);color:#fff}

/* FOOTER */
.footer{background:var(--black);border-top:1px solid rgba(255,255,255,.06);padding:18px 24px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;font-size:12px}
.footer-logo{display:flex;align-items:center;gap:8px;color:#fff;font-weight:700;letter-spacing:.04em}
.footer-copy{color:rgba(255,255,255,.2)}
</style>
</head>
<body>

<nav class="nav">
  <div class="nav-inner">
    <a href="/" class="nav-logo">
      <svg width="30" height="30" viewBox="0 0 100 100" fill="none">
        <circle cx="50" cy="50" r="46" stroke="#09090B" stroke-width="2.5"/>
        <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#09090B"/>
        <circle cx="46" cy="30" r="2.5" fill="#E11D48"/>
      </svg>
      <div>
        <div class="nav-logo-text">НУ, НИХАУ СЕБЕ!</div>
        <div class="nav-logo-sub">Образовательный центр</div>
      </div>
    </a>
    <div class="nav-right">
      <div class="lang-group">
        <?php foreach(['ru','kz','en'] as $l): ?>
          <a href="?lang=<?= $l ?>" class="lang-opt<?= getLang()===$l?' on':'' ?>"><?= t("lang_{$l}") ?></a>
        <?php endforeach; ?>
      </div>
      <a href="/login.php" class="btn-nav-login"><?= h(t('nav_login')) ?></a>
      <a href="/register.php" class="btn-nav-reg"><?= h(t('nav_register')) ?></a>
    </div>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="hero-eyebrow">Образовательный центр</div>
  <h1 class="hero-title">НУ, <span>НИ</span>ХАУ СЕБЕ!</h1>
  <p class="hero-sub"><?= h(t('hero_subtitle')) ?></p>
  <div class="hero-ctas">
    <a href="/register.php?role=student" class="btn-hero-primary">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      <?= h(t('hero_cta_student')) ?>
    </a>
    <a href="/register.php?role=teacher" class="btn-hero-outline">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
      <?= h(t('hero_cta_teacher')) ?>
    </a>
  </div>
</div>

<!-- FEATURES -->
<section class="section section-alt">
  <div class="section-inner">
    <div class="section-header">
      <p class="section-eyebrow">Личный кабинет</p>
      <h2 class="section-title">Всё в одном месте</h2>
    </div>
    <div class="features-grid">
      <?php foreach([
        ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', t('feature_1_title'), t('feature_1_desc')],
        ['M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', t('feature_2_title'), t('feature_2_desc')],
        ['M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', t('feature_3_title'), t('feature_3_desc')],
      ] as [$icon, $title, $desc]): ?>
      <div class="card feature-card">
        <div class="feature-icon">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/></svg>
        </div>
        <div class="feature-title"><?= h($title) ?></div>
        <div class="feature-desc"><?= h($desc) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="section">
  <div class="section-inner">
    <div class="section-header">
      <p class="section-eyebrow">Стоимость обучения</p>
      <h2 class="section-title">Прайс</h2>
    </div>
    <div class="tab-row">
      <button class="tab on" id="tb-off" onclick="switchTab('off')">Оффлайн</button>
      <button class="tab"    id="tb-on"  onclick="switchTab('on')">Онлайн</button>
    </div>

    <div id="pr-off">
      <div class="pricing-grid">
        <div class="card price-card">
          <div class="price-card-header">
            <div class="price-card-icon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#71717A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
            <div><div class="price-card-title">Индивидуальные</div></div>
          </div>
          <?php foreach([['8','72 000'],['12','96 000']] as [$n,$p]): ?>
          <div class="price-row"><span class="price-num"><?= $n ?></span><span style="font-size:12px;color:#A1A1AA;flex:1">занятий</span><div class="price-dots"></div><span class="price-amount"><?= $p ?> тг</span></div>
          <?php endforeach; ?>
        </div>
        <div class="card price-card">
          <div class="price-card-header">
            <div class="price-card-icon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#71717A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg></div>
            <div><div class="price-card-title">Групповые</div><div class="price-card-sub">до 4-х человек</div></div>
          </div>
          <?php foreach([['8','48 000'],['12','51 000']] as [$n,$p]): ?>
          <div class="price-row"><span class="price-num"><?= $n ?></span><span style="font-size:12px;color:#A1A1AA;flex:1">занятий</span><div class="price-dots"></div><span class="price-amount"><?= $p ?> тг</span></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div id="pr-on" style="display:none">
      <div class="pricing-grid">
        <div class="card price-card">
          <div class="price-card-header">
            <div class="price-card-icon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#71717A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
            <div><div class="price-card-title">Индивидуальные</div></div>
          </div>
          <?php foreach([['8','56 000'],['12','72 000']] as [$n,$p]): ?>
          <div class="price-row"><span class="price-num"><?= $n ?></span><span style="font-size:12px;color:#A1A1AA;flex:1">занятий</span><div class="price-dots"></div><span class="price-amount"><?= $p ?> тг</span></div>
          <?php endforeach; ?>
        </div>
        <div class="card price-card">
          <div class="price-card-header">
            <div class="price-card-icon"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#71717A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg></div>
            <div><div class="price-card-title">Групповые</div><div class="price-card-sub">до 4-х человек</div></div>
          </div>
          <?php foreach([['8','32 000'],['12','40 000']] as [$n,$p]): ?>
          <div class="price-row"><span class="price-num"><?= $n ?></span><span style="font-size:12px;color:#A1A1AA;flex:1">занятий</span><div class="price-dots"></div><span class="price-amount"><?= $p ?> тг</span></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HSK -->
<section class="section section-alt">
  <div class="section-inner">
    <div class="section-header">
      <p class="section-eyebrow">Подготовка к экзамену</p>
      <h2 class="section-title">HSK</h2>
      <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:14px">
        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:999px;border:1px solid var(--border);font-size:12px;color:var(--muted)">Группа до 3-х человек</span>
        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:999px;border:1px solid var(--border);font-size:12px;color:var(--muted)">Оффлайн · 12 занятий</span>
      </div>
    </div>
    <div class="card hsk-card">
      <?php foreach([['3','70 000'],['4','85 000'],['5','90 000'],['6','110 000']] as [$l,$p]): ?>
      <div class="hsk-row">
        <span class="hsk-badge">HSK <?= $l ?></span>
        <span style="font-size:12px;color:var(--muted)">уровень</span>
        <div class="hsk-dots"></div>
        <span style="font-size:18px;font-weight:800;color:var(--red)"><?= $p ?> тг</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section class="section">
  <div class="section-inner">
    <div class="section-header">
      <p class="section-eyebrow">Контакты</p>
      <h2 class="section-title">Где нас найти</h2>
    </div>
    <div class="contact-grid">
      <div class="card contact-card">
        <div class="contact-icon">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#71717A" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
        </div>
        <div class="contact-label">Режим работы</div>
        <div style="font-size:13px;color:var(--muted);margin-bottom:6px">Пн — Пт</div>
        <div style="font-size:20px;font-weight:800;color:var(--black)">10:00 — 20:00</div>
      </div>
      <div class="card contact-card">
        <div class="contact-icon">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#71717A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div class="contact-label">Телефон</div>
        <a href="tel:+77782593795" style="font-size:17px;font-weight:700;color:var(--black)">+7 778 259 37 95</a>
      </div>
      <div class="card contact-card">
        <div class="contact-icon">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#71717A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
        </div>
        <div class="contact-label">Адрес</div>
        <div style="font-size:14px;font-weight:600;color:var(--black);line-height:1.5">Коргалжинское шоссе 31,<br>2 этаж, н.п 18</div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<div class="cta-banner">
  <div class="cta-title"><?= h(t('cta_title')) ?></div>
  <div class="cta-sub"><?= h(t('cta_subtitle')) ?></div>
  <div class="cta-btns">
    <a href="/register.php" class="btn-cta-red"><?= h(t('nav_register')) ?></a>
    <a href="/login.php" class="btn-cta-ghost"><?= h(t('nav_login')) ?></a>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-logo">
    <svg width="20" height="20" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="white" stroke-width="2" opacity=".2"/><path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="white"/><circle cx="46" cy="30" r="2.5" fill="#E11D48"/></svg>
    НУ, НИХАУ СЕБЕ!
  </div>
  <div class="footer-copy">&copy; <?= date('Y') ?> Образовательный центр «Ну, Нихау Себе!»</div>
</footer>

<script>
function switchTab(t){
  document.getElementById('pr-off').style.display = t==='off'?'':'none';
  document.getElementById('pr-on').style.display  = t==='on' ?'':'none';
  document.getElementById('tb-off').classList.toggle('on', t==='off');
  document.getElementById('tb-on').classList.toggle('on',  t==='on');
}
</script>
</body>
</html>

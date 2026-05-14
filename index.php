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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#F9F2E4;
  --bg-alt:#EFE4CC;
  --card:#FFFDF6;
  --border:#DDD0B5;
  --orange:#C76432;
  --orange-dk:#A8521F;
  --orange-gl:rgba(199,100,50,.11);
  --navy:#1A2B54;
  --navy-dk:#0E1A33;
  --text:#24180A;
  --muted:#8B7355;
  --cream:#F5E8CB;
}
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);font-size:15px;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
button{font-family:inherit;cursor:pointer}

/* ── NAV ─────────────────────────────────────────── */
.nav{position:sticky;top:0;z-index:100;background:rgba(249,242,228,.88);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border-bottom:1px solid var(--border)}
.nav-in{max-width:1100px;margin:0 auto;padding:0 28px;height:64px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.logo{display:flex;align-items:center;gap:11px}
.logo-box{width:38px;height:38px;border-radius:10px;background:var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.logo-name{font-family:'Playfair Display',serif;font-size:13.5px;font-weight:700;color:var(--navy);line-height:1.15}
.logo-sub{font-size:9.5px;font-weight:700;color:var(--orange);letter-spacing:.08em;text-transform:uppercase}
.nav-r{display:flex;align-items:center;gap:8px}
.lang-row{display:flex;gap:2px;background:var(--bg-alt);border:1px solid var(--border);border-radius:8px;padding:3px}
.lang-a{display:block;padding:4px 11px;border-radius:5px;font-size:12px;font-weight:600;color:var(--muted);transition:all .12s}
.lang-a.on{background:var(--card);color:var(--navy);box-shadow:0 1px 4px rgba(0,0,0,.09)}
.btn-nav{padding:9px 20px;border-radius:9px;font-size:13px;font-weight:700;background:var(--navy);color:#fff;transition:background .15s;border:none}
.btn-nav:hover{background:var(--navy-dk)}

/* ── HERO ────────────────────────────────────────── */
.hero{background:var(--navy-dk);position:relative;overflow:hidden;padding:108px 28px 0}
.h-ring{position:absolute;border-radius:50%;pointer-events:none}
.h-ring-1{right:-90px;top:-90px;width:480px;height:480px;border:70px solid rgba(199,100,50,.06)}
.h-ring-2{left:-110px;bottom:20px;width:300px;height:300px;border:1px solid rgba(255,255,255,.04)}
.h-ring-3{right:180px;top:60px;width:140px;height:140px;border:1px solid rgba(199,100,50,.1)}
.h-dot{position:absolute;border-radius:50%;background:var(--orange)}
.hero-in{max-width:740px;margin:0 auto;text-align:center;position:relative;z-index:1}
.hero-badge{display:inline-flex;align-items:center;gap:7px;padding:6px 16px;border-radius:999px;border:1px solid rgba(199,100,50,.3);background:rgba(199,100,50,.09);color:var(--orange);font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:28px}
.badge-dot{width:5px;height:5px;background:var(--orange);border-radius:50%}
.hero-title{font-family:'Playfair Display',serif;font-size:clamp(46px,8.5vw,90px);font-weight:900;color:var(--cream);line-height:1.0;letter-spacing:-.025em;margin-bottom:24px}
.hero-title .hi{color:var(--orange)}
.hero-sub{font-size:17px;color:rgba(245,232,203,.45);line-height:1.7;max-width:460px;margin:0 auto 46px}
.hero-btn{display:inline-flex;align-items:center;gap:9px;background:var(--orange);color:#fff;padding:15px 34px;border-radius:11px;font-size:15px;font-weight:700;transition:all .18s;box-shadow:0 6px 30px rgba(199,100,50,.45);margin-bottom:68px}
.hero-btn:hover{background:var(--orange-dk);transform:translateY(-2px);box-shadow:0 10px 36px rgba(199,100,50,.55)}
.hero-wave{display:block;width:100%;height:68px;margin-bottom:-3px}

/* ── SHARED ──────────────────────────────────────── */
.section{padding:84px 28px}
.alt{background:var(--bg-alt)}
.si{max-width:1000px;margin:0 auto}
.sh{text-align:center;margin-bottom:50px}
.eyebrow{display:block;font-size:11px;font-weight:700;letter-spacing:.13em;text-transform:uppercase;color:var(--orange);margin-bottom:12px}
.stitle{font-family:'Playfair Display',serif;font-size:clamp(26px,3.8vw,40px);font-weight:800;color:var(--navy);letter-spacing:-.02em;line-height:1.18}

/* ── TEACHERS ────────────────────────────────────── */
.tc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
@media(max-width:740px){.tc-grid{grid-template-columns:1fr;max-width:380px;margin-left:auto;margin-right:auto}}
.tc-card{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;transition:box-shadow .22s,transform .22s}
.tc-card:hover{box-shadow:0 12px 40px rgba(26,43,84,.11);transform:translateY(-3px)}
.tc-photo{height:300px;overflow:hidden}
.tc-photo img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .45s}
.tc-card:hover .tc-photo img{transform:scale(1.05)}
.tc-body{padding:22px 24px 26px}
.tc-name{font-family:'Playfair Display',serif;font-size:22px;font-weight:800;color:var(--navy);margin-bottom:2px}
.tc-subj{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px}
.tc-badge{display:inline-block;padding:3px 12px;border-radius:7px;font-size:11px;font-weight:700;margin-bottom:16px}
.tc-b-native{background:var(--orange-gl);color:var(--orange);border:1px solid rgba(199,100,50,.22)}
.tc-b-hsk{background:rgba(26,43,84,.07);color:var(--navy);border:1px solid rgba(26,43,84,.14)}
.tc-facts{list-style:none;display:flex;flex-direction:column;gap:8px;margin-bottom:16px}
.tc-facts li{display:flex;align-items:center;gap:9px;font-size:13px;color:var(--text);line-height:1.4}
.tc-facts li::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--orange);flex-shrink:0}
.tc-tags{display:flex;flex-wrap:wrap;gap:5px}
.tc-tag{padding:3px 10px;border-radius:999px;border:1px solid var(--border);font-size:11px;color:var(--muted)}

/* ── FEATURES ────────────────────────────────────── */
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
@media(max-width:680px){.feat-grid{grid-template-columns:1fr}}
.feat-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:30px;transition:box-shadow .2s,transform .2s}
.feat-card:hover{box-shadow:0 10px 36px rgba(26,43,84,.09);transform:translateY(-3px)}
.ficon{width:46px;height:46px;background:var(--orange-gl);border-radius:13px;display:flex;align-items:center;justify-content:center;margin-bottom:20px}
.ficon svg{stroke:var(--orange)}
.ftitle{font-family:'Playfair Display',serif;font-size:17px;font-weight:700;color:var(--navy);margin-bottom:8px}
.fdesc{font-size:13px;color:var(--muted);line-height:1.68}

/* ── PRICING ─────────────────────────────────────── */
.tabs-w{display:flex;justify-content:center;margin-bottom:36px}
.tabs{display:flex;gap:4px;background:var(--card);border:1px solid var(--border);border-radius:11px;padding:4px}
.tab{padding:9px 28px;border-radius:8px;font-size:13px;font-weight:700;color:var(--muted);background:none;border:none;transition:all .15s}
.tab.on{background:var(--navy);color:#fff;box-shadow:0 2px 12px rgba(26,43,84,.22)}
.pr-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:680px;margin:0 auto}
@media(max-width:560px){.pr-grid{grid-template-columns:1fr}}
.pr-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:26px}
.pch{display:flex;align-items:center;gap:12px;margin-bottom:22px}
.pc-ico{width:40px;height:40px;border-radius:50%;background:var(--orange-gl);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.pc-ico svg{stroke:var(--orange)}
.pcn{font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--navy)}
.pcs{font-size:11px;color:var(--muted)}
.prow{display:flex;align-items:center;padding:11px 0;border-bottom:1px dashed var(--border)}
.prow:last-child{border:none;padding-bottom:0}
.pcnt{background:var(--navy);color:#fff;border-radius:6px;padding:3px 11px;font-size:12px;font-weight:700;flex-shrink:0;text-align:center}
.plbl{font-size:11px;color:var(--muted);margin-left:8px}
.pdots{flex:1;border-bottom:1px dashed var(--border);opacity:.3;margin:0 12px}
.pamt{font-family:'Playfair Display',serif;font-size:18px;font-weight:700;color:var(--orange);white-space:nowrap}

/* ── HSK ─────────────────────────────────────────── */
.hsk-wrap{background:var(--navy);padding:84px 28px}
.hsk-in{max-width:520px;margin:0 auto}
.hsk-head{text-align:center;margin-bottom:36px}
.hsk-head .eyebrow{color:rgba(199,100,50,.9)}
.hsk-head .stitle{color:var(--cream)}
.hsk-chips{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:14px}
.hsk-chip{padding:4px 14px;border-radius:999px;border:1px solid rgba(245,232,203,.12);font-size:12px;color:rgba(245,232,203,.38)}
.hsk-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:16px;overflow:hidden}
.hrow{display:flex;align-items:center;padding:17px 26px;border-bottom:1px solid rgba(255,255,255,.05)}
.hrow:last-child{border:none}
.hbadge{background:var(--orange);color:#fff;border-radius:7px;padding:5px 13px;font-size:12px;font-weight:700;flex-shrink:0;min-width:58px;text-align:center}
.hlbl{font-size:12px;color:rgba(245,232,203,.3);margin-left:10px}
.hdots{flex:1;border-bottom:1px dashed rgba(255,255,255,.08);margin:0 14px}
.hprice{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--orange)}

/* ── CONTACT ─────────────────────────────────────── */
.ct-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:820px;margin:0 auto}
@media(max-width:620px){.ct-grid{grid-template-columns:1fr}}
.ct-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:30px;text-align:center}
.ct-ico{width:46px;height:46px;border-radius:50%;background:var(--orange-gl);display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.ct-ico svg{stroke:var(--orange)}
.ct-lbl{font-size:10px;font-weight:700;letter-spacing:.11em;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.ct-val{font-family:'Playfair Display',serif;font-size:clamp(15px,2.2vw,21px);font-weight:700;color:var(--navy);line-height:1.3}
.ct-sub{font-size:12px;color:var(--muted);margin-top:5px}

/* ── CTA ─────────────────────────────────────────── */
.cta-wrap{background:var(--orange);padding:80px 28px;text-align:center;position:relative;overflow:hidden}
.cta-r{position:absolute;border-radius:50%;pointer-events:none}
.cta-r1{right:-70px;top:-80px;width:320px;height:320px;border:55px solid rgba(255,255,255,.08)}
.cta-r2{left:-60px;bottom:-90px;width:240px;height:240px;border:1px solid rgba(255,255,255,.1)}
.cta-in{position:relative;z-index:1}
.cta-title{font-family:'Playfair Display',serif;font-size:clamp(28px,5vw,48px);font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:10px}
.cta-sub{font-size:15px;color:rgba(255,255,255,.65);margin-bottom:36px}
.btn-cta{display:inline-flex;align-items:center;gap:9px;padding:14px 34px;border-radius:11px;font-size:15px;font-weight:700;background:var(--navy-dk);color:#fff;border:none;transition:all .18s;box-shadow:0 4px 22px rgba(0,0,0,.3)}
.btn-cta:hover{background:var(--navy);transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,0,0,.38)}

/* ── FOOTER ──────────────────────────────────────── */
.footer{background:var(--navy-dk);border-top:1px solid rgba(255,255,255,.04);padding:22px 28px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px}
.ft-logo{display:flex;align-items:center;gap:10px}
.ft-box{width:28px;height:28px;border-radius:7px;background:rgba(199,100,50,.16);display:flex;align-items:center;justify-content:center}
.ft-name{font-family:'Playfair Display',serif;font-size:13px;color:var(--cream);font-weight:700;letter-spacing:.02em}
.ft-copy{font-size:12px;color:rgba(245,232,203,.18)}
</style>
</head>
<body>

<!-- ── NAV ─────────────────────────────────────────── -->
<nav class="nav">
  <div class="nav-in">
    <a href="/" class="logo">
      <div class="logo-box">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M12 3C10.5 6 7 8.5 3 11L7.5 12C5.5 15.5 3 20 4.5 22L12 15L19.5 22C21 20 18.5 15.5 16.5 12L21 11C17 8.5 13.5 6 12 3Z" fill="#F5E8CB"/>
          <circle cx="11" cy="4" r="1.5" fill="#C76432"/>
        </svg>
      </div>
      <div>
        <div class="logo-name">НУ, НИХАУ СЕБЕ!</div>
        <div class="logo-sub">Образовательный центр</div>
      </div>
    </a>
    <div class="nav-r">
      <div class="lang-row">
        <?php foreach(['ru','kz','en'] as $l): ?>
          <a href="?lang=<?= $l ?>" class="lang-a<?= getLang()===$l?' on':'' ?>"><?= t("lang_{$l}") ?></a>
        <?php endforeach; ?>
      </div>
      <a href="/login.php" class="btn-nav"><?= h(t('nav_login')) ?></a>
    </div>
  </div>
</nav>

<!-- ── HERO ─────────────────────────────────────────── -->
<div class="hero">
  <div class="h-ring h-ring-1"></div>
  <div class="h-ring h-ring-2"></div>
  <div class="h-ring h-ring-3"></div>
  <div class="h-dot" style="width:6px;height:6px;left:7%;top:22%;opacity:.45"></div>
  <div class="h-dot" style="width:4px;height:4px;right:12%;top:48%;opacity:.25"></div>
  <div class="h-dot" style="width:3px;height:3px;left:22%;bottom:28%;opacity:.2"></div>

  <div class="hero-in">
    <div class="hero-badge"><span class="badge-dot"></span>Образовательный центр</div>
    <h1 class="hero-title">НУ,&nbsp;<span class="hi">НИ</span>ХАУ&nbsp;СЕБЕ!</h1>
    <p class="hero-sub"><?= h(t('hero_subtitle')) ?></p>
    <a href="/login.php" class="hero-btn">
      <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
      <?= h(t('nav_login')) ?>
    </a>
  </div>
  <svg class="hero-wave" viewBox="0 0 1440 68" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0,34 C200,68 440,0 720,28 C900,46 1160,4 1440,34 L1440,68 L0,68 Z" fill="#F9F2E4"/>
  </svg>
</div>

<!-- ── FEATURES ──────────────────────────────────────── -->
<section class="section">
  <div class="si">
    <div class="sh">
      <span class="eyebrow">Личный кабинет</span>
      <h2 class="stitle">Всё в одном месте</h2>
    </div>
    <div class="feat-grid">
      <?php foreach([
        ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', t('feature_1_title'), t('feature_1_desc')],
        ['M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', t('feature_2_title'), t('feature_2_desc')],
        ['M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', t('feature_3_title'), t('feature_3_desc')],
      ] as [$icon, $title, $desc]): ?>
      <div class="feat-card">
        <div class="ficon">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/>
          </svg>
        </div>
        <div class="ftitle"><?= h($title) ?></div>
        <div class="fdesc"><?= h($desc) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── TEACHERS ──────────────────────────────────────── -->
<section class="section alt">
  <div class="si">
    <div class="sh">
      <span class="eyebrow">Наша команда</span>
      <h2 class="stitle">Преподаватели</h2>
    </div>
    <div class="tc-grid">

      <div class="tc-card">
        <div class="tc-photo">
          <img src="/imgs/t1_y.jpg" alt="Учитель Ха" style="object-position:12% 8%">
        </div>
        <div class="tc-body">
          <div class="tc-name">Учитель Ха</div>
          <div class="tc-subj">Китайский язык</div>
          <span class="tc-badge tc-b-native">Носитель языка</span>
          <ul class="tc-facts">
            <li>Опыт преподавания более 2 лет</li>
            <li>Формат: Онлайн / Оффлайн</li>
            <li>Ученики от 10 лет и старше</li>
          </ul>
          <div class="tc-tags">
            <span class="tc-tag">Разговорная практика</span>
            <span class="tc-tag">Быстрый результат</span>
            <span class="tc-tag">Коммуникабельная</span>
          </div>
        </div>
      </div>

      <div class="tc-card">
        <div class="tc-photo">
          <img src="/imgs/t2.jpg" alt="Арайлым" style="object-position:78% 5%">
        </div>
        <div class="tc-body">
          <div class="tc-name">Арайлым</div>
          <div class="tc-subj">Китайский язык</div>
          <span class="tc-badge tc-b-hsk">HSK 5</span>
          <ul class="tc-facts">
            <li>Опыт преподавания более 2 лет</li>
            <li>Формат: Онлайн / Оффлайн</li>
            <li>Ученики от 10 лет и старше</li>
          </ul>
          <div class="tc-tags">
            <span class="tc-tag">Аудирование</span>
            <span class="tc-tag">Грамматика</span>
            <span class="tc-tag">Разговорная практика</span>
          </div>
        </div>
      </div>

      <div class="tc-card">
        <div class="tc-photo">
          <img src="/imgs/t3.jpg" alt="Кристина" style="object-position:78% 5%">
        </div>
        <div class="tc-body">
          <div class="tc-name">Кристина</div>
          <div class="tc-subj">Китайский язык</div>
          <span class="tc-badge tc-b-hsk">HSK 5</span>
          <ul class="tc-facts">
            <li>Опыт преподавания более 2 лет</li>
            <li>Формат: Онлайн</li>
            <li>Ученики от 10 лет и старше</li>
          </ul>
          <div class="tc-tags">
            <span class="tc-tag">Аудирование</span>
            <span class="tc-tag">Грамматика</span>
            <span class="tc-tag">Разговорная практика</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── PRICING ───────────────────────────────────────── -->
<section class="section alt">
  <div class="si">
    <div class="sh">
      <span class="eyebrow">Стоимость обучения</span>
      <h2 class="stitle">Прайс</h2>
    </div>
    <div class="tabs-w">
      <div class="tabs">
        <button class="tab on" id="tb-off" onclick="switchTab('off')">Оффлайн</button>
        <button class="tab"    id="tb-on"  onclick="switchTab('on')">Онлайн</button>
      </div>
    </div>

    <div id="pr-off">
      <div class="pr-grid">
        <div class="pr-card">
          <div class="pch">
            <div class="pc-ico"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
            <div><div class="pcn">Индивидуальные</div></div>
          </div>
          <?php foreach([['8','72 000'],['12','96 000']] as [$n,$p]): ?>
          <div class="prow"><span class="pcnt"><?= $n ?></span><span class="plbl">занятий</span><div class="pdots"></div><span class="pamt"><?= $p ?> тг</span></div>
          <?php endforeach; ?>
        </div>
        <div class="pr-card">
          <div class="pch">
            <div class="pc-ico"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg></div>
            <div><div class="pcn">Групповые</div><div class="pcs">до 4-х человек</div></div>
          </div>
          <?php foreach([['8','48 000'],['12','51 000']] as [$n,$p]): ?>
          <div class="prow"><span class="pcnt"><?= $n ?></span><span class="plbl">занятий</span><div class="pdots"></div><span class="pamt"><?= $p ?> тг</span></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div id="pr-on" style="display:none">
      <div class="pr-grid">
        <div class="pr-card">
          <div class="pch">
            <div class="pc-ico"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
            <div><div class="pcn">Индивидуальные</div></div>
          </div>
          <?php foreach([['8','56 000'],['12','72 000']] as [$n,$p]): ?>
          <div class="prow"><span class="pcnt"><?= $n ?></span><span class="plbl">занятий</span><div class="pdots"></div><span class="pamt"><?= $p ?> тг</span></div>
          <?php endforeach; ?>
        </div>
        <div class="pr-card">
          <div class="pch">
            <div class="pc-ico"><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg></div>
            <div><div class="pcn">Групповые</div><div class="pcs">до 4-х человек</div></div>
          </div>
          <?php foreach([['8','32 000'],['12','40 000']] as [$n,$p]): ?>
          <div class="prow"><span class="pcnt"><?= $n ?></span><span class="plbl">занятий</span><div class="pdots"></div><span class="pamt"><?= $p ?> тг</span></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── HSK ────────────────────────────────────────────── -->
<div class="hsk-wrap">
  <div class="hsk-in">
    <div class="hsk-head">
      <span class="eyebrow">Подготовка к экзамену</span>
      <h2 class="stitle" style="color:var(--cream)">HSK</h2>
      <div class="hsk-chips">
        <span class="hsk-chip">Группа до 3-х человек</span>
        <span class="hsk-chip">Оффлайн · 12 занятий</span>
      </div>
    </div>
    <div class="hsk-card">
      <?php foreach([['3','70 000'],['4','85 000'],['5','90 000'],['6','110 000']] as [$l,$p]): ?>
      <div class="hrow">
        <span class="hbadge">HSK <?= $l ?></span>
        <span class="hlbl">уровень</span>
        <div class="hdots"></div>
        <span class="hprice"><?= $p ?> тг</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── CONTACT ──────────────────────────────────────── -->
<section class="section">
  <div class="si">
    <div class="sh">
      <span class="eyebrow">Контакты</span>
      <h2 class="stitle">Где нас найти</h2>
    </div>
    <div class="ct-grid">
      <div class="ct-card">
        <div class="ct-ico">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
        </div>
        <div class="ct-lbl">Режим работы</div>
        <div class="ct-val">10:00 — 20:00</div>
        <div class="ct-sub">Понедельник — Пятница</div>
      </div>
      <div class="ct-card">
        <div class="ct-ico">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div class="ct-lbl">Телефон</div>
        <a href="tel:+77782593795" class="ct-val">+7 778 259 37 95</a>
      </div>
      <div class="ct-card">
        <div class="ct-ico">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
        </div>
        <div class="ct-lbl">Адрес</div>
        <div class="ct-val">Коргалжинское шоссе 31</div>
        <div class="ct-sub">2 этаж, н.п 18</div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ───────────────────────────────────────────── -->
<div class="cta-wrap">
  <div class="cta-r cta-r1"></div>
  <div class="cta-r cta-r2"></div>
  <div class="cta-in">
    <div class="cta-title"><?= h(t('cta_title')) ?></div>
    <div class="cta-sub"><?= h(t('cta_subtitle')) ?></div>
    <a href="/login.php" class="btn-cta">
      <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
      <?= h(t('nav_login')) ?>
    </a>
  </div>
</div>

<!-- ── FOOTER ────────────────────────────────────────── -->
<footer class="footer">
  <div class="ft-logo">
    <div class="ft-box">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
        <path d="M12 3C10.5 6 7 8.5 3 11L7.5 12C5.5 15.5 3 20 4.5 22L12 15L19.5 22C21 20 18.5 15.5 16.5 12L21 11C17 8.5 13.5 6 12 3Z" fill="#C76432"/>
      </svg>
    </div>
    <span class="ft-name">НУ, НИХАУ СЕБЕ!</span>
  </div>
  <div class="ft-copy">&copy; <?= date('Y') ?> Образовательный центр «Ну, Нихау Себе!»</div>
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

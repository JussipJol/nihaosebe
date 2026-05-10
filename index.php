<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

if (isset($_GET['lang'])) { setLang($_GET['lang']); header('Location: /'); exit; }
if (isLoggedIn()) {
    $u = currentUser();
    header('Location: ' . ($u['role'] === 'teacher' ? '/teacher/' : '/student/'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= getLang() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>НУ, НИХАУ СЕБЕ! — Образовательный центр</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
* { font-family: 'Segoe UI', system-ui, sans-serif; }
body { background: #fff; color: #1A1510; }

.btn-primary {
  display: inline-flex; align-items: center; justify-content: center; gap: 10px;
  background: #1A1510; color: #C9A84C; padding: 14px 32px;
  border-radius: 14px; font-weight: 800; font-size: 15px;
  text-decoration: none; transition: opacity .15s;
}
.btn-primary:hover { opacity: .85; }

.btn-outline {
  display: inline-flex; align-items: center; justify-content: center; gap: 10px;
  background: #fff; color: #1A1510; padding: 14px 32px;
  border-radius: 14px; font-weight: 800; font-size: 15px;
  border: 2px solid #1A1510; text-decoration: none; transition: background .15s;
}
.btn-outline:hover { background: #f5f5f5; }

.card { background: #fff; border: 1px solid #EDE5D4; border-radius: 20px; }

.price-row {
  display: flex; align-items: center; padding: 14px 0;
  border-bottom: 1px dashed #EDE5D4;
}
.price-row:last-child { border-bottom: none; }

.tab { padding: 9px 28px; border-radius: 50px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all .15s; border: 2px solid #1A1510; color: #1A1510; background: #fff; }
.tab.active { background: #1A1510; color: #C9A84C; }

.section-alt { background: #FAFAF9; }

.logo-svg { display: inline-block; }
</style>
</head>
<body>

<!-- NAV -->
<nav style="border-bottom: 1px solid #EDE5D4; position: sticky; top: 0; background: rgba(255,255,255,.95); backdrop-filter: blur(10px); z-index: 50;">
  <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="/" class="flex items-center gap-3 text-decoration-none">
      <svg class="logo-svg" width="36" height="36" viewBox="0 0 100 100" fill="none">
        <circle cx="50" cy="50" r="46" stroke="#1A1510" stroke-width="3"/>
        <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#1A1510"/>
        <circle cx="46" cy="30" r="2.5" fill="#C0392B"/>
      </svg>
      <div>
        <div style="font-weight:900;font-size:13px;letter-spacing:.12em;color:#1A1510;line-height:1.1">НУ, НИХАУ СЕБЕ!</div>
        <div style="font-size:10px;color:#C9A84C;font-weight:600;letter-spacing:.08em">ОБРАЗОВАТЕЛЬНЫЙ ЦЕНТР</div>
      </div>
    </a>

    <div class="flex items-center gap-4">
      <div class="flex gap-1 p-1 rounded-full" style="background:#F5F0E8">
        <?php foreach (['ru','kz','en'] as $l): ?>
          <a href="?lang=<?= $l ?>" style="padding:5px 14px;border-radius:50px;font-size:12px;font-weight:700;text-decoration:none;transition:all .15s;<?= getLang()===$l ? 'background:#1A1510;color:#C9A84C' : 'color:#7B6F5E' ?>">
            <?= t("lang_{$l}") ?>
          </a>
        <?php endforeach; ?>
      </div>
      <a href="/login.php" style="font-weight:600;font-size:14px;color:#1A1510;text-decoration:none" class="hidden sm:block"><?= h(t('nav_login')) ?></a>
      <a href="/register.php" class="btn-primary" style="padding:9px 22px;font-size:14px;border-radius:10px"><?= h(t('nav_register')) ?></a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section style="padding: 80px 24px 72px; text-align: center;">
  <div style="max-width: 640px; margin: 0 auto;">
    <svg class="logo-svg" width="80" height="80" viewBox="0 0 100 100" fill="none" style="margin-bottom:28px">
      <circle cx="50" cy="50" r="46" stroke="#1A1510" stroke-width="2.5"/>
      <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#1A1510"/>
      <circle cx="46" cy="30" r="2.5" fill="#C0392B"/>
    </svg>

    <div style="font-size:11px;font-weight:700;letter-spacing:.25em;color:#C9A84C;text-transform:uppercase;margin-bottom:12px">
      Образовательный центр
    </div>

    <h1 style="font-size:clamp(36px,8vw,68px);font-weight:900;color:#1A1510;line-height:1;margin-bottom:20px;letter-spacing:-.02em">
      НУ, НИХАУ СЕБЕ!
    </h1>

    <p style="font-size:18px;color:#7B6F5E;line-height:1.6;margin-bottom:40px;max-width:480px;margin-left:auto;margin-right:auto">
      <?= h(t('hero_subtitle')) ?>
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="/register.php?role=student" class="btn-primary">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <?= h(t('hero_cta_student')) ?>
      </a>
      <a href="/register.php?role=teacher" class="btn-outline">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <?= h(t('hero_cta_teacher')) ?>
      </a>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section-alt" style="padding:64px 24px;border-top:1px solid #EDE5D4;border-bottom:1px solid #EDE5D4">
  <div style="max-width:900px;margin:0 auto">
    <div style="text-align:center;margin-bottom:40px">
      <p style="font-size:11px;font-weight:700;letter-spacing:.2em;color:#C9A84C;text-transform:uppercase;margin-bottom:8px">Личный кабинет</p>
      <h2 style="font-size:32px;font-weight:900;color:#1A1510">Всё в одном месте</h2>
    </div>
    <div class="grid sm:grid-cols-3 gap-5">
      <?php
      $features = [
        ['icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'title'=>t('feature_1_title'), 'desc'=>t('feature_1_desc')],
        ['icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title'=>t('feature_2_title'), 'desc'=>t('feature_2_desc')],
        ['icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'title'=>t('feature_3_title'), 'desc'=>t('feature_3_desc')],
      ];
      foreach ($features as $f): ?>
      <div class="card" style="padding:28px">
        <div style="width:48px;height:48px;background:#1A1510;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
          <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $f['icon'] ?>"/></svg>
        </div>
        <h3 style="font-size:17px;font-weight:800;color:#1A1510;margin-bottom:8px"><?= h($f['title']) ?></h3>
        <p style="font-size:14px;color:#7B6F5E;line-height:1.6"><?= h($f['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PRICING -->
<section style="padding:72px 24px">
  <div style="max-width:680px;margin:0 auto">
    <div style="text-align:center;margin-bottom:36px">
      <p style="font-size:11px;font-weight:700;letter-spacing:.2em;color:#C9A84C;text-transform:uppercase;margin-bottom:8px">Стоимость обучения</p>
      <h2 style="font-size:40px;font-weight:900;color:#1A1510;margin-bottom:4px">ПРАЙС</h2>
      <p style="font-size:12px;color:#7B6F5E;letter-spacing:.1em;text-transform:uppercase">Образовательный центр «НУ, НИХАУ СЕБЕ!»</p>
    </div>

    <div style="display:flex;gap:8px;justify-content:center;margin-bottom:32px">
      <button class="tab active" id="tab-offline" onclick="switchTab('offline')">Оффлайн</button>
      <button class="tab"        id="tab-online"  onclick="switchTab('online')">Онлайн</button>
    </div>

    <!-- OFFLINE -->
    <div id="prices-offline">
      <div style="text-align:center;margin-bottom:20px">
        <span style="background:#1A1510;color:#C9A84C;border-radius:50px;padding:6px 20px;font-size:12px;font-weight:700;letter-spacing:.08em">ФОРМАТ: ОФФЛАЙН</span>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">

        <div class="card" style="padding:24px">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
            <div style="width:40px;height:40px;border:2px solid #C9A84C;border-radius:50%;display:flex;align-items:center;justify-content:center">
              <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
              <div style="font-weight:800;font-size:13px;text-transform:uppercase;color:#1A1510">Индивидуальные</div>
            </div>
          </div>
          <?php foreach ([['8','72 000'],['12','96 000']] as [$n,$p]): ?>
          <div class="price-row">
            <span style="background:#1A1510;color:#fff;border-radius:6px;padding:3px 10px;font-weight:900;font-size:14px;min-width:32px;text-align:center"><?= $n ?></span>
            <span style="flex:1;color:#C0C0B0;font-size:12px;padding:0 10px">занятий ············</span>
            <span style="font-weight:900;font-size:18px;color:#C0392B"><?= $p ?> тг</span>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="card" style="padding:24px">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
            <div style="width:40px;height:40px;border:2px solid #C9A84C;border-radius:50%;display:flex;align-items:center;justify-content:center">
              <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            </div>
            <div>
              <div style="font-weight:800;font-size:13px;text-transform:uppercase;color:#1A1510">Групповые</div>
              <div style="font-size:11px;color:#C9A84C;font-weight:600">до 4-х человек</div>
            </div>
          </div>
          <?php foreach ([['8','48 000'],['12','51 000']] as [$n,$p]): ?>
          <div class="price-row">
            <span style="background:#1A1510;color:#fff;border-radius:6px;padding:3px 10px;font-weight:900;font-size:14px;min-width:32px;text-align:center"><?= $n ?></span>
            <span style="flex:1;color:#C0C0B0;font-size:12px;padding:0 10px">занятий ············</span>
            <span style="font-weight:900;font-size:18px;color:#C0392B"><?= $p ?> тг</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ONLINE -->
    <div id="prices-online" style="display:none">
      <div style="text-align:center;margin-bottom:20px">
        <span style="background:#C9A84C;color:#1A1510;border-radius:50px;padding:6px 20px;font-size:12px;font-weight:700;letter-spacing:.08em">ФОРМАТ: ОНЛАЙН</span>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">

        <div class="card" style="padding:24px">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
            <div style="width:40px;height:40px;border:2px solid #C9A84C;border-radius:50%;display:flex;align-items:center;justify-content:center">
              <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div><div style="font-weight:800;font-size:13px;text-transform:uppercase;color:#1A1510">Индивидуальные</div></div>
          </div>
          <?php foreach ([['8','56 000'],['12','72 000']] as [$n,$p]): ?>
          <div class="price-row">
            <span style="background:#1A1510;color:#fff;border-radius:6px;padding:3px 10px;font-weight:900;font-size:14px"><?= $n ?></span>
            <span style="flex:1;color:#C0C0B0;font-size:12px;padding:0 10px">занятий ············</span>
            <span style="font-weight:900;font-size:18px;color:#C0392B"><?= $p ?> тг</span>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="card" style="padding:24px">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
            <div style="width:40px;height:40px;border:2px solid #C9A84C;border-radius:50%;display:flex;align-items:center;justify-content:center">
              <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            </div>
            <div>
              <div style="font-weight:800;font-size:13px;text-transform:uppercase;color:#1A1510">Групповые</div>
              <div style="font-size:11px;color:#C9A84C;font-weight:600">до 4-х человек</div>
            </div>
          </div>
          <?php foreach ([['8','32 000'],['12','40 000']] as [$n,$p]): ?>
          <div class="price-row">
            <span style="background:#1A1510;color:#fff;border-radius:6px;padding:3px 10px;font-weight:900;font-size:14px"><?= $n ?></span>
            <span style="flex:1;color:#C0C0B0;font-size:12px;padding:0 10px">занятий ············</span>
            <span style="font-weight:900;font-size:18px;color:#C0392B"><?= $p ?> тг</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HSK -->
<section class="section-alt" style="padding:72px 24px;border-top:1px solid #EDE5D4;border-bottom:1px solid #EDE5D4">
  <div style="max-width:520px;margin:0 auto">
    <div style="text-align:center;margin-bottom:28px">
      <p style="font-size:11px;font-weight:700;letter-spacing:.2em;color:#C9A84C;text-transform:uppercase;margin-bottom:8px">Подготовка к сдаче экзамена</p>
      <h2 style="font-size:48px;font-weight:900;color:#1A1510;margin-bottom:12px">HSK</h2>
      <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center">
        <span style="border:1px solid #EDE5D4;border-radius:50px;padding:5px 14px;font-size:12px;color:#1A1510;display:flex;align-items:center;gap:6px">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
          Группа до 3-х человек
        </span>
        <span style="border:1px solid #EDE5D4;border-radius:50px;padding:5px 14px;font-size:12px;color:#1A1510">📍 Оффлайн</span>
        <span style="border:1px solid #EDE5D4;border-radius:50px;padding:5px 14px;font-size:12px;color:#1A1510">12 занятий</span>
      </div>
    </div>

    <div class="card" style="padding:24px">
      <?php foreach ([['3','70 000'],['4','85 000'],['5','90 000'],['6','110 000']] as [$lvl,$price]): ?>
      <div class="price-row">
        <div style="background:#1A1510;color:#C9A84C;border-radius:8px;width:52px;height:36px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:13px;flex-shrink:0">
          HSK <?= $lvl ?>
        </div>
        <span style="flex:1;color:#C0C0B0;font-size:12px;padding:0 12px">уровень ·············</span>
        <span style="font-weight:900;font-size:20px;color:#C0392B"><?= $price ?> тг</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section style="padding:72px 24px">
  <div style="max-width:860px;margin:0 auto">
    <div style="text-align:center;margin-bottom:40px">
      <p style="font-size:11px;font-weight:700;letter-spacing:.2em;color:#C9A84C;text-transform:uppercase;margin-bottom:8px">Приходите к нам</p>
      <h2 style="font-size:32px;font-weight:900;color:#1A1510">Контакты</h2>
    </div>
    <div class="grid sm:grid-cols-3 gap-5">

      <div class="card" style="padding:28px;text-align:center">
        <div style="font-size:11px;font-weight:700;letter-spacing:.15em;color:#C9A84C;text-transform:uppercase;margin-bottom:16px">Режим работы</div>
        <div style="font-size:13px;font-weight:600;color:#7B6F5E;margin-bottom:12px">Пн — Пт</div>
        <div style="display:flex;align-items:center;justify-content:center;gap:16px">
          <div style="text-align:center">
            <div style="font-size:11px;margin-bottom:4px">☀️</div>
            <div style="font-weight:900;font-size:24px;color:#1A1510">10:00</div>
          </div>
          <div style="width:1px;height:36px;background:#EDE5D4"></div>
          <div style="text-align:center">
            <div style="font-size:11px;margin-bottom:4px">🌙</div>
            <div style="font-weight:900;font-size:24px;color:#1A1510">20:00</div>
          </div>
        </div>
      </div>

      <div class="card" style="padding:28px;text-align:center">
        <div style="width:44px;height:44px;border:2px solid #C9A84C;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div style="font-size:11px;font-weight:700;letter-spacing:.12em;color:#7B6F5E;text-transform:uppercase;margin-bottom:10px">Телефон</div>
        <a href="tel:+77782593795" style="font-weight:900;font-size:16px;color:#1A1510;text-decoration:none">+7 778 259 37 95</a>
      </div>

      <div class="card" style="padding:28px;text-align:center">
        <div style="width:44px;height:44px;border:2px solid #C9A84C;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#C9A84C" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div style="font-size:11px;font-weight:700;letter-spacing:.12em;color:#7B6F5E;text-transform:uppercase;margin-bottom:10px">Адрес</div>
        <p style="font-weight:700;font-size:14px;color:#1A1510;line-height:1.5">Коргалжинское шоссе 31,<br>2 этаж, н.п 18</p>
      </div>

    </div>
  </div>
</section>

<!-- CTA -->
<section style="background:#1A1510;padding:64px 24px;text-align:center">
  <svg width="56" height="56" viewBox="0 0 100 100" fill="none" style="margin:0 auto 20px">
    <circle cx="50" cy="50" r="46" stroke="#C9A84C" stroke-width="2"/>
    <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#C9A84C"/>
    <circle cx="46" cy="30" r="2.5" fill="#C0392B"/>
  </svg>
  <h2 style="font-size:36px;font-weight:900;color:#fff;margin-bottom:8px"><?= h(t('cta_title')) ?></h2>
  <p style="color:#C9A84C;font-size:15px;margin-bottom:32px"><?= h(t('cta_subtitle')) ?></p>
  <div class="flex flex-col sm:flex-row gap-4 justify-center">
    <a href="/register.php" style="background:#C9A84C;color:#1A1510;padding:14px 36px;border-radius:12px;font-weight:900;font-size:15px;text-decoration:none"><?= h(t('nav_register')) ?></a>
    <a href="/login.php" style="border:2px solid #C9A84C;color:#C9A84C;padding:14px 36px;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none"><?= h(t('nav_login')) ?></a>
  </div>
</section>

<!-- FOOTER -->
<footer style="background:#1A1510;border-top:1px solid #2D2519;padding:20px 24px">
  <div style="max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px">
    <div style="display:flex;align-items:center;gap:10px">
      <svg width="24" height="24" viewBox="0 0 100 100" fill="none">
        <circle cx="50" cy="50" r="46" stroke="#C9A84C" stroke-width="2"/>
        <path d="M50 26C47 32 38 37 26 43L39 46C33 55 26 67 29 74L50 54L71 74C74 67 67 55 61 46L74 43C62 37 53 32 50 26Z" fill="#C9A84C"/>
      </svg>
      <span style="font-weight:900;font-size:12px;letter-spacing:.12em;color:#FAF6EF">НУ, НИХАУ СЕБЕ!</span>
    </div>
    <p style="color:#C9A84C;opacity:.4;font-size:12px">&copy; <?= date('Y') ?> Образовательный центр «Ну, Нихау Себе!»</p>
  </div>
</footer>

<script>
function switchTab(t) {
  document.getElementById('prices-offline').style.display = t==='offline' ? '' : 'none';
  document.getElementById('prices-online').style.display  = t==='online'  ? '' : 'none';
  document.getElementById('tab-offline').classList.toggle('active', t==='offline');
  document.getElementById('tab-online').classList.toggle('active', t==='online');
}
</script>
</body>
</html>

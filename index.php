<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$lang = resolve_lang($_GET['lang'] ?? null);
if ($lang === null) {
    http_response_code(404);
    readfile(__DIR__ . '/404.html');
    exit;
}
lang($lang);

$canonical = absolute_url(lang_path());
$ogImage = absolute_url('/assets/img/og-image.jpg');
$gallery = gallery_items();
$players = t('demo.players');
$scores = [3, 1, 4, 2, 5, 2, 3, 1];
$contactStatus = is_string($_GET['contact'] ?? null) ? (t('contact.status')[$_GET['contact']] ?? null) : null;
$storeLinks = array_values(array_filter([config('stores.google_play'), config('stores.app_store')]));

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => absolute_url('/#organization'),
            'name' => 'KissOkey',
            'url' => absolute_url(),
            'logo' => absolute_url('/assets/icons/icon-512.png'),
            'sameAs' => $storeLinks,
        ],
        [
            '@type' => 'WebSite',
            '@id' => absolute_url('/#website'),
            'url' => absolute_url(),
            'name' => 'KissOkey',
            'inLanguage' => array_keys(config('langs')),
            'publisher' => ['@id' => absolute_url('/#organization')],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => t('meta.title'),
            'description' => t('meta.description'),
            'inLanguage' => $lang,
            'isPartOf' => ['@id' => absolute_url('/#website')],
            'about' => ['@id' => absolute_url('/#game')],
            'primaryImageOfPage' => $ogImage,
        ],
        [
            '@type' => ['VideoGame', 'MobileApplication'],
            '@id' => absolute_url('/#game'),
            'name' => 'KissOkey',
            'description' => t('meta.description'),
            'url' => $canonical,
            'image' => $ogImage,
            'genre' => ['Party', 'Casual', 'Social'],
            'gamePlatform' => ['Android', 'iOS'],
            'operatingSystem' => 'Android, iOS',
            'applicationCategory' => 'GameApplication',
            'playMode' => 'MultiPlayer',
            'numberOfPlayers' => ['@type' => 'QuantitativeValue', 'minValue' => 2, 'maxValue' => 8],
            'inLanguage' => $lang,
            'author' => ['@id' => absolute_url('/#organization')],
            'publisher' => ['@id' => absolute_url('/#organization')],
            'installUrl' => $storeLinks,
            'sameAs' => $storeLinks,
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $canonical . '#faq',
            'inLanguage' => $lang,
            'mainEntity' => array_map(static fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
            ], t('faq.items')),
        ],
    ],
];

header('Content-Type: text/html; charset=UTF-8');
header('Content-Language: ' . $lang);
?>
<!doctype html>
<html lang="<?= e($lang) ?>" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= te('meta.title') ?></title>
    <meta name="description" content="<?= te('meta.description') ?>">
    <meta name="keywords" content="<?= te('meta.keywords') ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="<?= e($canonical) ?>">
<?php foreach (config('langs') as $code => $info): ?>
    <link rel="alternate" hreflang="<?= e($code) ?>" href="<?= e(absolute_url($info['path'])) ?>">
<?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= e(absolute_url(lang_path(config('x_default_lang')))) ?>">

    <meta name="theme-color" content="<?= e(config('theme_color')) ?>">
    <meta name="color-scheme" content="light">
    <meta name="format-detection" content="telephone=no">
    <meta name="application-name" content="KissOkey">
    <meta name="apple-mobile-web-app-title" content="KissOkey">
<?php if (config('stores.app_store_id')): ?>
    <meta name="apple-itunes-app" content="app-id=<?= e(config('stores.app_store_id')) ?>">
<?php endif; ?>

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="KissOkey">
    <meta property="og:title" content="<?= te('meta.title') ?>">
    <meta property="og:description" content="<?= te('meta.description') ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= te('meta.og_alt') ?>">
    <meta property="og:locale" content="<?= e(config('langs')[$lang]['locale']) ?>">
<?php foreach (config('langs') as $code => $info): if ($code === $lang) continue; ?>
    <meta property="og:locale:alternate" content="<?= e($info['locale']) ?>">
<?php endforeach; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= te('meta.title') ?>">
    <meta name="twitter:description" content="<?= te('meta.description') ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <meta name="twitter:image:alt" content="<?= te('meta.og_alt') ?>">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="preload" href="/assets/fonts/fredoka-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <script type="module" src="<?= e(asset('js/main.js')) ?>"></script>
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</head>
<body>
<a class="skip-link" href="#main"><?= te('nav.skip') ?></a>

<svg class="sprite" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="g-kiss" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff7ab8"/><stop offset=".55" stop-color="#e2337c"/><stop offset="1" stop-color="#a80f57"/></linearGradient>
        <radialGradient id="g-bomb" cx=".35" cy=".3" r=".85"><stop offset="0" stop-color="#8ae2ff"/><stop offset=".45" stop-color="#3b6fe0"/><stop offset="1" stop-color="#5b2391"/></radialGradient>
        <linearGradient id="g-heart" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff8cc4"/><stop offset="1" stop-color="#e2337c"/></linearGradient>
        <linearGradient id="g-star" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7fe0ff"/><stop offset="1" stop-color="#1f9fe0"/></linearGradient>
        <linearGradient id="g-burst" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffe27a"/><stop offset=".5" stop-color="#ff9f43"/><stop offset="1" stop-color="#ff4f9a"/></linearGradient>
        <linearGradient id="g-glass" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#7fdcff"/><stop offset=".5" stop-color="#3b8de8"/><stop offset="1" stop-color="#4a2fb0"/></linearGradient>
    </defs>
    <symbol id="i-kiss" viewBox="0 0 64 64">
        <path d="M4 32C10 24 16 15 23 15c4 0 7 3 9 6 2-3 5-6 9-6 7 0 13 9 19 17-10 2-18-2-28 1-10-3-18 1-28-1z" fill="url(#g-kiss)" stroke="#fff" stroke-width="2.5" stroke-linejoin="round"/>
        <path d="M4 32c10 3 18 0 28 3 10-3 18 0 28-3-6 14-16 22-28 22S10 46 4 32z" fill="url(#g-kiss)" stroke="#fff" stroke-width="2.5" stroke-linejoin="round"/>
        <path d="M17 43c4 3 9 4 14 4M20 21.5c2-1.5 4-2 6-1.5" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" opacity=".6"/>
    </symbol>
    <symbol id="i-bomb" viewBox="0 0 64 64">
        <path d="M46 17c1-5 4-8 9-8.5" fill="none" stroke="#d9a35f" stroke-width="3" stroke-linecap="round"/>
        <rect x="37" y="16" width="13" height="10" rx="3" transform="rotate(45 43.5 21)" fill="#3b2a80" stroke="#fff" stroke-width="2"/>
        <circle cx="28" cy="38" r="21" fill="url(#g-bomb)" stroke="#fff" stroke-width="2.5"/>
        <ellipse cx="20" cy="29" rx="7" ry="4" transform="rotate(-38 20 29)" fill="#fff" opacity=".6"/>
        <polygon points="56,2 56.9,6.3 60.6,3.9 58.2,7.6 62.5,8.5 58.2,9.4 60.6,13.1 56.9,10.7 56,15 55.1,10.7 51.4,13.1 53.8,9.4 49.5,8.5 53.8,7.6 51.4,3.9 55.1,6.3" fill="#ffd34d"/>
    </symbol>
    <symbol id="i-heart" viewBox="0 0 24 24">
        <path d="M12 21s-7.5-4.6-9.6-9.3C.9 8.3 3 4.5 6.7 4.5c2.1 0 3.6 1.1 5.3 3 1.7-1.9 3.2-3 5.3-3 3.7 0 5.8 3.8 4.3 7.2C19.5 16.4 12 21 12 21z" fill="url(#g-heart)"/>
        <ellipse cx="7.6" cy="9" rx="2" ry="1.3" transform="rotate(-30 7.6 9)" fill="#fff" opacity=".6"/>
    </symbol>
    <symbol id="i-star" viewBox="0 0 24 24">
        <polygon points="12,1.6 14.7,8.9 22.5,9.2 16.4,14 18.5,21.5 12,17.2 5.5,21.5 7.6,14 1.5,9.2 9.3,8.9" fill="url(#g-star)" stroke="url(#g-star)" stroke-width="1.5" stroke-linejoin="round"/>
    </symbol>
    <symbol id="i-burst" viewBox="0 0 24 24">
        <polygon points="12,0.5 13.7,6.8 18.8,2.7 16.4,8.8 22.9,8.4 17.5,12 22.9,15.6 16.4,15.2 18.8,21.3 13.7,17.2 12,23.5 10.3,17.2 5.2,21.3 7.6,15.2 1.1,15.6 6.5,12 1.1,8.4 7.6,8.8 5.2,2.7 10.3,6.8" fill="url(#g-burst)" stroke="#fff" stroke-width=".8" stroke-linejoin="round"/>
    </symbol>
    <symbol id="i-sparkle" viewBox="0 0 24 24">
        <path d="M12 0c1 6.5 5 10.5 12 12-7 1.5-11 5.5-12 12-1-6.5-5-10.5-12-12C7 10.5 11 6.5 12 0z" fill="currentColor"/>
    </symbol>
    <symbol id="i-bottle" viewBox="0 0 40 116">
        <path d="M14 4h12v6h-1v24c0 8 11 13 11 24v50a6 6 0 0 1-6 6H10a6 6 0 0 1-6-6V58c0-11 11-16 11-24V10h-1z" fill="url(#g-glass)" stroke="#fff" stroke-width="2.5" stroke-linejoin="round"/>
        <rect x="12.5" y="1.5" width="15" height="8" rx="3" fill="#ff4f9a" stroke="#fff" stroke-width="2"/>
        <path d="M9.5 64v36" stroke="#fff" stroke-width="3.5" stroke-linecap="round" opacity=".55"/>
        <use href="#i-heart" x="12" y="72" width="16" height="16"/>
    </symbol>
    <symbol id="i-face" viewBox="0 0 48 48">
        <circle cx="17" cy="21" r="3.2" fill="#3b0f45"/><circle cx="31" cy="21" r="3.2" fill="#3b0f45"/>
        <circle cx="18.2" cy="19.8" r="1" fill="#fff"/><circle cx="32.2" cy="19.8" r="1" fill="#fff"/>
        <circle cx="12" cy="28" r="3" fill="#ff5fa2" opacity=".45"/><circle cx="36" cy="28" r="3" fill="#ff5fa2" opacity=".45"/>
        <path d="M18 29c3 3.5 9 3.5 12 0" fill="none" stroke="#3b0f45" stroke-width="2.6" stroke-linecap="round"/>
    </symbol>
    <symbol id="i-play" viewBox="0 0 24 24">
        <path d="M3.5 1.6 13.3 12 3.5 22.4z" fill="#00c3ff"/>
        <path d="M3.5 1.6 16.5 9.33 13.3 12z" fill="#00e676"/>
        <path d="M3.5 22.4 13.3 12l3.2 2.67z" fill="#ff3d57"/>
        <path d="M16.5 9.33 21 12l-4.5 2.67-3.2-2.67z" fill="#ffc400"/>
    </symbol>
    <symbol id="i-apple" viewBox="0 0 24 24">
        <path fill="currentColor" d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/>
    </symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6.5 6.5-6.5s6.5 2.9 6.5 6.5"/><circle cx="17" cy="9" r="2.8"/><path d="M16.8 13.6c2.9.3 4.9 2.8 4.9 5.9"/></g></symbol>
    <symbol id="i-globe" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/></g></symbol>
    <symbol id="i-eye" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></g></symbol>
    <symbol id="i-bolt" viewBox="0 0 24 24"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/></symbol>
    <symbol id="i-device" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/></g></symbol>
    <symbol id="i-spin" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></g></symbol>
    <symbol id="i-send" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4 20-7z"/><path d="M22 2 11 13"/></g></symbol>
    <symbol id="i-chev-l" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    <symbol id="i-chev-r" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    <symbol id="i-close" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></symbol>
</svg>

<header class="header">
    <div class="container header__inner">
        <a class="brand" href="<?= e(lang_path()) ?>" aria-label="<?= te('nav.home') ?>">
            <img class="brand__mark" src="<?= e(asset('img/logo-mark.webp')) ?>" width="44" height="44" alt="">
            <span class="brand__name" aria-hidden="true"><span class="brand__kiss">Kiss</span><span class="brand__okey">Okey</span></span>
        </a>

        <nav class="nav" id="site-nav" popover aria-label="<?= te('nav.label') ?>">
            <ul class="nav__list">
                <li><a href="#how"><?= te('nav.how') ?></a></li>
                <li><a href="#rules"><?= te('nav.rules') ?></a></li>
                <li><a href="#features"><?= te('nav.features') ?></a></li>
                <li><a href="#gallery"><?= te('nav.gallery') ?></a></li>
                <li><a href="#faq"><?= te('nav.faq') ?></a></li>
                <li><a href="#contact"><?= te('nav.contact') ?></a></li>
            </ul>
            <button class="icon-btn nav__close" type="button" popovertarget="site-nav" popovertargetaction="hide" aria-label="<?= te('nav.close') ?>">
                <svg aria-hidden="true"><use href="#i-close"/></svg>
            </button>
        </nav>

        <div class="header__actions">
            <div class="lang-switch" role="group" aria-label="<?= te('nav.language') ?>">
<?php foreach (config('langs') as $code => $info): ?>
                <a href="<?= e($info['path']) ?>" hreflang="<?= e($code) ?>" lang="<?= e($code) ?>" title="<?= e($info['name']) ?>"<?= $code === $lang ? ' aria-current="page"' : '' ?>><?= e($info['short']) ?></a>
<?php endforeach; ?>
            </div>
            <a class="btn btn--small header__cta" href="#download"><?= te('nav.download') ?></a>
            <button class="icon-btn menu-toggle" type="button" popovertarget="site-nav" aria-label="<?= te('nav.menu') ?>">
                <svg aria-hidden="true"><use href="#i-menu"/></svg>
            </button>
        </div>
    </div>
</header>

<main id="main">
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__inner">
            <div class="hero__copy">
                <p class="eyebrow"><svg aria-hidden="true"><use href="#i-sparkle"/></svg><?= te('hero.eyebrow') ?></p>
                <h1 class="hero__title" id="hero-title"><?= t('hero.title_html') ?></h1>
                <p class="hero__lead"><?= te('hero.lead') ?></p>
                <?php render_store_buttons() ?>
                <ul class="chips">
<?php foreach (t('hero.chips') as $chip): ?>
                    <li class="chip"><svg aria-hidden="true"><use href="#i-<?= e($chip['icon']) ?>"/></svg><?= e($chip['text']) ?></li>
<?php endforeach; ?>
                </ul>
            </div>

            <div class="hero__art">
                <div class="hero__halo" aria-hidden="true"></div>
                <div class="hero__ring" aria-hidden="true"></div>
                <img class="hero__logo" src="<?= e(asset('img/logo.webp')) ?>" width="512" height="512" alt="<?= te('hero.logo_alt') ?>" fetchpriority="high">
                <svg class="float float--1" aria-hidden="true"><use href="#i-heart"/></svg>
                <svg class="float float--2" aria-hidden="true"><use href="#i-star"/></svg>
                <svg class="float float--3" aria-hidden="true"><use href="#i-kiss"/></svg>
                <svg class="float float--4" aria-hidden="true"><use href="#i-bomb"/></svg>
                <svg class="float float--5" aria-hidden="true"><use href="#i-heart"/></svg>
                <svg class="float float--6" aria-hidden="true"><use href="#i-burst"/></svg>
            </div>
        </div>
    </section>

    <section class="section" id="how" aria-labelledby="how-title">
        <div class="container">
            <header class="section__head reveal">
                <p class="eyebrow"><?= te('how.eyebrow') ?></p>
                <h2 id="how-title"><?= te('how.title') ?></h2>
                <p class="section__lead"><?= te('how.lead') ?></p>
            </header>
            <ol class="steps">
<?php foreach (t('how.steps') as $i => $step): ?>
                <li class="step card reveal">
                    <span class="step__num" aria-hidden="true"><?= $i + 1 ?></span>
                    <span class="step__icon step__icon--<?= $i + 1 ?>" aria-hidden="true">
<?php if ($i === 0): ?>
                        <svg><use href="#i-users"/></svg>
<?php elseif ($i === 1): ?>
                        <svg class="step__bottle"><use href="#i-bottle"/></svg>
<?php else: ?>
                        <svg class="step__duo"><use href="#i-kiss"/></svg><svg class="step__duo"><use href="#i-bomb"/></svg>
<?php endif; ?>
                    </span>
                    <h3><?= e($step['title']) ?></h3>
                    <p><?= e($step['text']) ?></p>
                </li>
<?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="section section--stage" id="rules" aria-labelledby="rules-title">
        <div class="container rules">
            <div class="demo card reveal" data-demo data-i18n="<?= e(json_encode([
                'spin' => t('demo.spin'),
                'spinning' => t('demo.spinning'),
                'kk' => t('demo.result_kk'),
                'bk' => t('demo.result_bk'),
                'bb' => t('demo.result_bb'),
                'score' => t('demo.score'),
            ], JSON_UNESCAPED_UNICODE)) ?>">
                <p class="demo__label"><span class="live-dot" aria-hidden="true"></span><?= te('demo.label') ?></p>
                <div class="table" aria-hidden="true">
                    <div class="table__felt"></div>
                    <div class="bottle"><svg viewBox="0 0 40 116"><use href="#i-bottle"/></svg></div>
                    <div class="seats">
<?php foreach ($players as $i => $player): ?>
                        <div class="seat"><span class="avatar"><svg viewBox="0 0 48 48"><use href="#i-face"/></svg></span><span class="seat__name"><?= e($player) ?></span><span class="seat__score"><?= $scores[$i] ?></span></div>
<?php endforeach; ?>
                    </div>
                </div>
                <p class="demo__result" data-demo-result><?= te('demo.idle') ?></p>
                <button class="btn" type="button" data-demo-spin hidden><svg aria-hidden="true"><use href="#i-spin"/></svg><span data-label><?= te('demo.spin') ?></span></button>
            </div>

            <div class="rules__copy">
                <header class="section__head section__head--left reveal">
                    <p class="eyebrow"><?= te('rules.eyebrow') ?></p>
                    <h2 id="rules-title"><?= te('rules.title') ?></h2>
                    <p class="section__lead"><?= te('rules.lead') ?></p>
                </header>
                <ul class="rule-list">
<?php foreach (t('rules.items') as $key => $rule): ?>
                    <li class="rule rule--<?= e($key) ?> card reveal" data-rule="<?= e($key) ?>">
                        <span class="rule__combo" aria-hidden="true"><svg><use href="#i-<?= e($rule['combo'][0]) ?>"/></svg><b>+</b><svg><use href="#i-<?= e($rule['combo'][1]) ?>"/></svg></span>
                        <div class="rule__body">
                            <h3><?= e($rule['title']) ?></h3>
                            <p><?= e($rule['text']) ?></p>
                            <span class="rule__tag"><?= e($rule['tag']) ?></span>
                        </div>
                    </li>
<?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <section class="section" id="features" aria-labelledby="features-title">
        <div class="container">
            <header class="section__head reveal">
                <p class="eyebrow"><?= str_replace('KissOkey', '<span lang="en">KissOkey</span>', te('features.eyebrow')) ?></p>
                <h2 id="features-title"><?= te('features.title') ?></h2>
                <p class="section__lead"><?= te('features.lead') ?></p>
            </header>
            <ul class="features">
<?php foreach (t('features.items') as $feature): ?>
                <li class="feature card reveal">
                    <span class="feature__icon" aria-hidden="true"><svg><use href="#i-<?= e($feature['icon']) ?>"/></svg></span>
                    <h3><?= e($feature['title']) ?></h3>
                    <p><?= e($feature['text']) ?></p>
                </li>
<?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="section section--gallery" id="gallery" aria-labelledby="gallery-title">
        <div class="container">
            <header class="section__head reveal">
                <p class="eyebrow"><?= te('gallery.eyebrow') ?></p>
                <h2 id="gallery-title"><?= te('gallery.title') ?></h2>
                <p class="section__lead"><?= te('gallery.lead') ?></p>
            </header>
        </div>
        <div class="gallery" data-gallery>
<?php if ($gallery): ?>
            <button class="icon-btn gallery__nav gallery__nav--prev" type="button" data-gallery-prev aria-label="<?= te('gallery.prev') ?>" hidden><svg aria-hidden="true"><use href="#i-chev-l"/></svg></button>
            <ul class="gallery__track" data-gallery-track tabindex="0" aria-label="<?= te('gallery.region') ?>">
<?php foreach ($gallery as $i => $item): ?>
                <li class="shot">
                    <a href="<?= e($item['src']) ?>" data-lightbox data-width="<?= $item['width'] ?>" data-height="<?= $item['height'] ?>" aria-label="<?= te('gallery.open', ['{alt}' => $item['alt']]) ?>">
                        <img src="<?= e($item['thumb']['src'] ?? $item['src']) ?>"<?php if ($item['thumb']): ?> srcset="<?= e($item['thumb']['src']) ?> <?= $item['thumb']['width'] ?>w, <?= e($item['src']) ?> <?= $item['width'] ?>w" sizes="calc(min(58vh, 540px) * <?= sprintf('%.3F', $item['width'] / $item['height']) ?>)"<?php endif; ?> width="<?= $item['width'] ?>" height="<?= $item['height'] ?>" alt="<?= e($item['alt']) ?>" loading="lazy" decoding="async">
                    </a>
                </li>
<?php endforeach; ?>
            </ul>
            <button class="icon-btn gallery__nav gallery__nav--next" type="button" data-gallery-next aria-label="<?= te('gallery.next') ?>" hidden><svg aria-hidden="true"><use href="#i-chev-r"/></svg></button>
<?php else: ?>
            <ul class="gallery__track gallery__track--empty">
<?php foreach (['kiss', 'bomb', 'heart', 'star'] as $icon): ?>
                <li class="shot shot--placeholder">
                    <img src="<?= e(asset('img/logo-mark.webp')) ?>" width="96" height="96" alt="" loading="lazy">
                    <svg class="shot__icon" aria-hidden="true"><use href="#i-<?= $icon ?>"/></svg>
                    <p><?= te('gallery.empty') ?></p>
                </li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
        </div>
    </section>

    <section class="cta" id="download" aria-labelledby="cta-title">
        <div class="container">
            <div class="cta__card reveal">
                <svg class="cta__float cta__float--1" aria-hidden="true"><use href="#i-heart"/></svg>
                <svg class="cta__float cta__float--2" aria-hidden="true"><use href="#i-sparkle"/></svg>
                <svg class="cta__float cta__float--3" aria-hidden="true"><use href="#i-star"/></svg>
                <img class="cta__mark" src="<?= e(asset('img/logo-mark.webp')) ?>" width="104" height="104" alt="" loading="lazy">
                <h2 id="cta-title"><?= te('cta.title') ?></h2>
                <p><?= te('cta.text') ?></p>
                <?php render_store_buttons('center') ?>
            </div>
        </div>
    </section>

    <section class="section" id="faq" aria-labelledby="faq-title">
        <div class="container">
            <header class="section__head reveal">
                <p class="eyebrow"><?= te('faq.eyebrow') ?></p>
                <h2 id="faq-title"><?= te('faq.title') ?></h2>
            </header>
            <div class="faq">
<?php foreach (t('faq.items') as $i => $item): ?>
                <details class="faq__item card reveal" name="faq"<?= $i === 0 ? ' open' : '' ?>>
                    <summary><?= e($item['q']) ?></summary>
                    <p><?= e($item['a']) ?></p>
                </details>
<?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section" id="contact" aria-labelledby="contact-title">
        <div class="container contact">
            <div class="contact__intro">
                <header class="section__head section__head--left reveal">
                    <p class="eyebrow"><?= te('contact.eyebrow') ?></p>
                    <h2 id="contact-title"><?= te('contact.title') ?></h2>
                    <p class="section__lead"><?= te('contact.lead') ?></p>
                </header>
                <div class="contact__art" aria-hidden="true">
                    <svg class="contact__kiss"><use href="#i-kiss"/></svg>
                    <svg class="contact__bomb"><use href="#i-bomb"/></svg>
                    <svg class="contact__heart"><use href="#i-heart"/></svg>
                </div>
            </div>

            <form class="form card reveal" action="/contact.php" method="post" data-contact-form data-i18n="<?= e(json_encode([
                'submit' => t('contact.submit'),
                'sending' => t('contact.sending'),
                'error' => t('contact.status')['error'],
            ], JSON_UNESCAPED_UNICODE)) ?>">
                <input type="hidden" name="lang" value="<?= e($lang) ?>">
                <input type="hidden" name="_t" value="<?= e(form_token()) ?>">
                <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <div class="field-row">
                    <div class="field">
                        <label for="cf-name"><?= te('contact.name') ?></label>
                        <input id="cf-name" name="name" type="text" required minlength="2" maxlength="80" autocomplete="name" aria-describedby="cf-name-error">
                        <p class="field__error" id="cf-name-error" data-error-for="name" hidden></p>
                    </div>
                    <div class="field">
                        <label for="cf-email"><?= te('contact.email') ?></label>
                        <input id="cf-email" name="email" type="email" required maxlength="254" autocomplete="email" inputmode="email" aria-describedby="cf-email-error">
                        <p class="field__error" id="cf-email-error" data-error-for="email" hidden></p>
                    </div>
                </div>
                <div class="field">
                    <label for="cf-subject"><?= te('contact.subject') ?></label>
                    <select id="cf-subject" name="subject" required aria-describedby="cf-subject-error">
                        <option value="" disabled selected><?= te('contact.subject_placeholder') ?></option>
<?php foreach (t('contact.subjects') as $value => $label): ?>
                        <option value="<?= e($value) ?>"><?= e($label) ?></option>
<?php endforeach; ?>
                    </select>
                    <p class="field__error" id="cf-subject-error" data-error-for="subject" hidden></p>
                </div>
                <div class="field">
                    <label for="cf-message"><?= te('contact.message') ?></label>
                    <textarea id="cf-message" name="message" rows="5" required minlength="10" maxlength="5000" placeholder="<?= te('contact.message_placeholder') ?>" aria-describedby="cf-message-error"></textarea>
                    <p class="field__error" id="cf-message-error" data-error-for="message" hidden></p>
                </div>
                <label class="check">
                    <input type="checkbox" name="consent" value="1" required aria-describedby="cf-consent-error">
                    <span><?= te('contact.consent') ?></span>
                </label>
                <p class="field__error" id="cf-consent-error" data-error-for="consent" hidden></p>

                <button class="btn btn--block" type="submit"><svg aria-hidden="true"><use href="#i-send"/></svg><span data-label><?= te('contact.submit') ?></span></button>
                <p class="form-status<?= $contactStatus ? ($_GET['contact'] === 'sent' ? ' is-success' : ' is-error') : '' ?>" role="status" data-form-status<?= $contactStatus ? '' : ' hidden' ?>><?= e($contactStatus) ?></p>
            </form>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container footer__inner">
        <a class="brand" href="<?= e(lang_path()) ?>" aria-label="<?= te('nav.home') ?>">
            <img class="brand__mark" src="<?= e(asset('img/logo-mark.webp')) ?>" width="44" height="44" alt="" loading="lazy">
            <span class="brand__name" aria-hidden="true"><span class="brand__kiss">Kiss</span><span class="brand__okey">Okey</span></span>
        </a>
        <p class="footer__tagline"><?= te('footer.tagline') ?></p>
        <ul class="footer__links">
            <li><a href="#how"><?= te('nav.how') ?></a></li>
            <li><a href="#rules"><?= te('nav.rules') ?></a></li>
            <li><a href="#gallery"><?= te('nav.gallery') ?></a></li>
            <li><a href="#faq"><?= te('nav.faq') ?></a></li>
            <li><a href="#contact"><?= te('nav.contact') ?></a></li>
        </ul>
        <?php render_store_buttons('center') ?>
        <div class="footer__bottom">
            <p>&copy; <?= date('Y') ?> KissOkey. <?= te('footer.rights') ?></p>
            <div class="lang-switch" role="group" aria-label="<?= te('footer.language') ?>">
<?php foreach (config('langs') as $code => $info): ?>
                <a href="<?= e($info['path']) ?>" hreflang="<?= e($code) ?>" lang="<?= e($code) ?>"<?= $code === $lang ? ' aria-current="page"' : '' ?>><?= e($info['name']) ?></a>
<?php endforeach; ?>
            </div>
        </div>
    </div>
</footer>

<dialog class="lightbox" data-lightbox-dialog aria-label="<?= te('gallery.title') ?>">
    <figure class="lightbox__figure">
        <img data-lightbox-img src="data:," alt="">
        <figcaption data-lightbox-caption></figcaption>
    </figure>
    <p class="lightbox__counter" data-lightbox-counter></p>
    <button class="icon-btn lightbox__close" type="button" data-lightbox-close aria-label="<?= te('gallery.close') ?>"><svg aria-hidden="true"><use href="#i-close"/></svg></button>
    <button class="icon-btn lightbox__nav lightbox__nav--prev" type="button" data-lightbox-prev aria-label="<?= te('gallery.prev') ?>"><svg aria-hidden="true"><use href="#i-chev-l"/></svg></button>
    <button class="icon-btn lightbox__nav lightbox__nav--next" type="button" data-lightbox-next aria-label="<?= te('gallery.next') ?>"><svg aria-hidden="true"><use href="#i-chev-r"/></svg></button>
</dialog>

<?php foreach (config('langs') as $code => $info): if ($code === $lang) continue; ?>
<aside class="lang-hint" lang="<?= e($code) ?>" data-lang-hint="<?= e($code) ?>" hidden>
    <p><?= e(t('langhint.text', [], $code)) ?></p>
    <a class="btn btn--small" href="<?= e($info['path']) ?>" hreflang="<?= e($code) ?>"><?= e(t('langhint.action', [], $code)) ?></a>
    <button class="icon-btn icon-btn--ghost" type="button" data-lang-hint-close aria-label="<?= e(t('langhint.dismiss', [], $code)) ?>"><svg aria-hidden="true"><use href="#i-close"/></svg></button>
</aside>
<?php endforeach; ?>
</body>
</html>
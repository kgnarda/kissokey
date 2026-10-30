<?php

declare(strict_types=1);

mb_internal_encoding('UTF-8');

require __DIR__ . '/gallery.php';


function config(string $key): mixed
{
    static $config = null;
    $config ??= require __DIR__ . '/config.php';
    $value = $config;
    foreach (explode('.', $key) as $segment) $value = is_array($value) ? ($value[$segment] ?? null) : null;
    return $value;
}


function lang(?string $set = null): string
{
    static $current = null;
    if ($set !== null) $current = $set;
    return $current ?? config('default_lang');
}

function resolve_lang(mixed $requested): ?string
{
    if ($requested === null) return config('default_lang');
    return is_string($requested) && isset(config('langs')[$requested]) ? $requested : null;
}

function t(string $key, array $replace = [], ?string $lang = null): mixed
{
    static $dictionaries = [];
    $lang ??= lang();
    $dictionaries[$lang] ??= require __DIR__ . "/lang/{$lang}.php";
    $value = $dictionaries[$lang][$key] ?? $key;
    return $replace && is_string($value) ? strtr($value, $replace) : $value;
}


function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function te(string $key, array $replace = []): string
{
    return e(t($key, $replace));
}


function lang_path(?string $lang = null): string
{
    return config('langs')[$lang ?? lang()]['path'];
}

function absolute_url(string $path = '/'): string
{
    return rtrim(config('site_url'), '/') . $path;
}

function asset(string $path): string
{
    $file = dirname(__DIR__) . '/assets/' . $path;
    return '/assets/' . $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}


function form_token(): string
{
    $time = (string) time();
    return $time . '.' . hash_hmac('sha256', $time, config('contact.secret'));
}

function form_token_age(mixed $token): ?int
{
    if (!is_string($token)) return null;
    [$time, $signature] = array_pad(explode('.', $token, 2), 2, '');
    if (!ctype_digit($time) || !hash_equals(hash_hmac('sha256', $time, config('contact.secret')), $signature)) return null;
    return time() - (int) $time;
}


function render_store_buttons(string $modifier = ''): void
{
    ?>
    <div class="stores<?= $modifier ? ' stores--' . e($modifier) : '' ?>">
        <a class="store" href="<?= e(config('stores.google_play')) ?>" target="_blank" rel="noopener" aria-label="<?= te('store.google_label') ?>" data-store="google_play">
            <svg class="store__icon" aria-hidden="true"><use href="#i-play"/></svg>
            <span class="store__text"><small><?= te('store.google_small') ?></small><strong>Google Play</strong></span>
        </a>
        <a class="store" href="<?= e(config('stores.app_store')) ?>" target="_blank" rel="noopener" aria-label="<?= te('store.apple_label') ?>" data-store="app_store">
            <svg class="store__icon store__icon--apple" aria-hidden="true"><use href="#i-apple"/></svg>
            <span class="store__text"><small><?= te('store.apple_small') ?></small><strong>App Store</strong></span>
        </a>
    </div>
    <?php
}
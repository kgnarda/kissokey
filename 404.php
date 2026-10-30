<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

http_response_code(404);
header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex');
?>
<!doctype html>
<html lang="tr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 – Sayfa bulunamadı | KissOkey</title>
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#ec4e93">
    <link rel="icon" href="<?= e(url('favicon.ico')) ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= e(url('apple-touch-icon.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="notfound">
<main class="notfound__card card">
    <img src="<?= e(asset('img/logo-mark.webp')) ?>" width="120" height="120" alt="KissOkey">
    <h1><span class="text-kiss">4</span><span class="text-bomb">0</span><span class="text-kiss">4</span></h1>
    <p>Şişe bu sayfayı göstermedi! Aradığın sayfa bulunamadı.</p>
    <p lang="en">The bottle did not land on this page. The page you are looking for does not exist.</p>
    <div class="notfound__actions">
        <a class="btn" href="<?= e(lang_url('tr')) ?>">Ana sayfa</a>
        <a class="btn btn--blue" href="<?= e(lang_url('en')) ?>" lang="en">Home</a>
    </div>
</main>
</body>
</html>
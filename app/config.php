<?php

declare(strict_types=1);

// Edit these values before going live; every URL and e-mail on the site is read from here.
return [
    'site_url' => 'https://kissokey.com',
    'default_lang' => 'tr',
    'x_default_lang' => 'en',
    'langs' => [
        'tr' => ['name' => 'Türkçe', 'short' => 'TR', 'locale' => 'tr_TR', 'path' => '/'],
        'en' => ['name' => 'English', 'short' => 'EN', 'locale' => 'en_US', 'path' => '/en/'],
    ],
    'theme_color' => '#ec4e93',
    'stores' => [
        'google_play' => 'https://play.google.com/store/apps/details?id=com.kissokey.game',
        'app_store' => 'https://apps.apple.com/app/kissokey/id0000000000',
        // Numeric App Store ID; enables the Safari Smart App Banner when set.
        'app_store_id' => '',
    ],
    'contact' => [
        'to' => 'info@kissokey.com',
        'from' => 'no-reply@kissokey.com',
        'subjects' => ['general', 'support', 'bug', 'business'],
        'rate_limit' => 3,
        'rate_window' => 600,
        'min_fill_seconds' => 3,
        //>*>*> Override with the KISSOKEY_FORM_SECRET environment variable if this repository is public.
        'secret' => getenv('KISSOKEY_FORM_SECRET') ?: '4960e24338bfc4b85167748d364f561c9789043c3df51c6a',
    ],
    'gallery' => [
        'thumb_width' => 600,
        'thumb_quality' => 82,
    ],
];
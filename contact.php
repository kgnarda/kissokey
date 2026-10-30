<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

lang(resolve_lang($_POST['lang'] ?? null) ?? config('default_lang'));
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . lang_url() . '#contact', true, 303);
    exit;
}


// Answers with JSON for fetch() requests and falls back to a redirect for plain form posts.
function respond(string $status, array $errors = []): never
{
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        http_response_code(match ($status) { 'sent' => 200, 'invalid' => 422, 'rate' => 429, 'spam' => 400, default => 500 });
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => $status === 'sent', 'message' => t('contact.status')[$status], 'errors' => (object) $errors], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Location: ' . lang_url() . '?contact=' . $status . '#contact', true, 303);
    exit;
}

function post(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) && mb_check_encoding($value, 'UTF-8') ? trim($value) : '';
}


function rate_limited(): bool
{
    $dir = __DIR__ . '/app/storage';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true)) return false;

    $now = time();
    $window = (int) config('contact.rate_window');
    if (random_int(1, 50) === 1) foreach (glob("$dir/rl-*.json") ?: [] as $stale) if (filemtime($stale) < $now - $window) @unlink($stale);

    // IPs are stored only as salted hashes.
    $file = "$dir/rl-" . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . config('contact.secret')) . '.json';
    $hits = array_filter(json_decode((string) @file_get_contents($file), true) ?: [], static fn ($hit): bool => is_int($hit) && $hit > $now - $window);
    if (count($hits) >= (int) config('contact.rate_limit')) return true;

    $hits[] = $now;
    @file_put_contents($file, json_encode(array_values($hits)), LOCK_EX);
    return false;
}

function send_mail(string $name, string $email, string $subject, string $message): bool
{
    $subjectLine = '[KissOkey] ' . t('contact.subjects')[$subject] . ' – ' . $name;
    $body = implode("\r\n", [
        "Name: $name",
        "E-mail: $email",
        'Subject: ' . t('contact.subjects')[$subject],
        'Language: ' . lang(),
        'Date: ' . date('Y-m-d H:i:s T'),
        'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? '-'),
        '',
        str_repeat('-', 48),
        '',
        str_replace("\n", "\r\n", $message),
    ]);

    return mail(
        config('contact.to'),
        '=?UTF-8?B?' . base64_encode($subjectLine) . '?=',
        str_replace("\r\n", "\n", quoted_printable_encode($body)),
        [
            'From' => 'KissOkey <' . config('contact.from') . '>',
            'Reply-To' => '=?UTF-8?B?' . base64_encode($name) . "?= <$email>",
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => 'quoted-printable',
            'X-Mailer' => 'KissOkey Website',
        ],
        '-f' . config('contact.from')
    );
}


//>*>*> Bots that fill the hidden field get a fake success so they do not adapt.
if (post('website') !== '') respond('sent');

$age = form_token_age($_POST['_t'] ?? null);
if ($age === null || $age < (int) config('contact.min_fill_seconds') || $age > 86400) respond('spam');

$name = preg_replace('/\s+/u', ' ', post('name'));
$email = post('email');
$subject = post('subject');
$message = str_replace(["\r\n", "\r"], "\n", post('message'));

$errors = array_filter([
    'name' => mb_strlen($name) < 2 || mb_strlen($name) > 80,
    'email' => strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL),
    'subject' => !in_array($subject, config('contact.subjects'), true),
    'message' => mb_strlen($message) < 10 || mb_strlen($message) > 5000,
    'consent' => post('consent') !== '1',
]);
if ($errors) respond('invalid', array_intersect_key(t('contact.errors'), $errors));
if (rate_limited()) respond('rate');

if (!send_mail($name, $email, $subject, $message)) {
    error_log('KissOkey contact form: mail() failed for ' . config('contact.to'));
    respond('error');
}
respond('sent');
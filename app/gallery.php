<?php

declare(strict_types=1);

// Any image dropped into /assets/gallery is listed automatically, ordered by natural file name (01-, 02-, ...).
function gallery_items(): array
{
    $dir = dirname(__DIR__) . '/assets/gallery';
    $captions = is_file("$dir/captions.json") ? (json_decode((string) file_get_contents("$dir/captions.json"), true) ?: []) : [];
    $files = array_filter(scandir($dir) ?: [], static fn (string $file): bool => (bool) preg_match('/\.(jpe?g|png|webp|avif|gif)$/i', $file));
    natcasesort($files);

    $items = [];
    foreach ($files as $file) {
        $path = "$dir/$file";
        $size = @getimagesize($path);
        if (!$size) continue;
        $items[] = [
            'src' => '/assets/gallery/' . rawurlencode($file) . '?v=' . filemtime($path),
            'thumb' => gallery_thumb($path, $size[0]),
            'width' => $size[0],
            'height' => $size[1],
            'alt' => $captions[$file][lang()] ?? gallery_alt($file, count($items) + 1),
        ];
    }
    return $items;
}

function gallery_alt(string $file, int $index): string
{
    $label = trim(preg_replace(['/^\d+[-_ ]*/', '/[-_]+/'], ['', ' '], pathinfo($file, PATHINFO_FILENAME)));
    if ($label === '') return t('gallery.alt_prefix') . ' ' . $index;
    return t('gallery.alt_prefix') . ': ' . mb_strtoupper(mb_substr($label, 0, 1)) . mb_substr($label, 1);
}


function gallery_thumb(string $path, int $width): ?array
{
    $target = (int) config('gallery.thumb_width');
    if ($width <= $target || !function_exists('imagewebp')) return null;

    $name = pathinfo($path, PATHINFO_FILENAME) . "-{$target}.webp";
    $thumb = dirname($path) . '/thumbs/' . $name;
    if ((!is_file($thumb) || filemtime($thumb) < filemtime($path)) && !gallery_render_thumb($path, $thumb, $target)) return null;
    return ['src' => '/assets/gallery/thumbs/' . rawurlencode($name) . '?v=' . filemtime($thumb), 'width' => $target];
}

//>*>*> Runs once per new or changed image; large PNG screenshots can briefly need ~4x their pixel count in memory.
function gallery_render_thumb(string $source, string $target, int $width): bool
{
    $image = @imagecreatefromstring((string) file_get_contents($source));
    $scaled = $image ? imagescale($image, $width, -1, IMG_BICUBIC) : false;
    if (!$scaled || (!is_dir(dirname($target)) && !@mkdir(dirname($target), 0755, true))) return false;

    imagesavealpha($scaled, true);
    $tmp = $target . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@imagewebp($scaled, $tmp, (int) config('gallery.thumb_quality')) && @rename($tmp, $target)) return true;
    @unlink($tmp);
    return false;
}
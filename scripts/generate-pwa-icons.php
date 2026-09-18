<?php

$iconsDir = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'icons';
if (! is_dir($iconsDir)) {
    mkdir($iconsDir, 0777, true);
}

function hexColor($im, string $hex): int|false
{
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    return imagecolorallocate($im, $r, $g, $b);
}

function paintIcon(string $path, int $size, string $bgHex, ?string $bannerHex, ?string $bannerLabel): void
{
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, true);
    imagesavealpha($im, true);
    imagefilledrectangle($im, 0, 0, $size, $size, hexColor($im, $bgHex));

    $white = imagecolorallocate($im, 255, 255, 255);
    $letterSize = (int) max(5, $size * 0.28);
    $bbox = imagettfbbox($letterSize, 0, fontPath(), 'B');
    if ($bbox) {
        $textW = $bbox[2] - $bbox[0];
        $textH = $bbox[1] - $bbox[7];
        $x = (int) (($size - $textW) / 2);
        $y = (int) (($size + $textH) / 2) - (int) ($size * 0.06);
        imagettftext($im, $letterSize, 0, $x, $y, $white, fontPath(), 'B');
    } else {
        imagestring($im, 5, (int) ($size * 0.42), (int) ($size * 0.38), 'B', $white);
    }

    if ($bannerHex && $bannerLabel) {
        $bannerH = (int) ($size * 0.22);
        $top = $size - $bannerH;
        imagefilledrectangle($im, 0, $top, $size, $size, hexColor($im, $bannerHex));
        $labelSize = (int) max(8, $size * 0.075);
        $bbox = imagettfbbox($labelSize, 0, fontPath(), $bannerLabel);
        if ($bbox) {
            $textW = $bbox[2] - $bbox[0];
            $textH = $bbox[1] - $bbox[7];
            $x = (int) (($size - $textW) / 2);
            $y = $top + (int) (($bannerH + $textH) / 2);
            imagettftext($im, $labelSize, 0, $x, $y, $white, fontPath(), $bannerLabel);
        } else {
            imagestring($im, 5, 8, $top + 6, $bannerLabel, $white);
        }
    }

    imagepng($im, $path);
    imagedestroy($im);
}

function fontPath(): string
{
    $candidates = [
        'C:\\Windows\\Fonts\\arialbd.ttf',
        'C:\\Windows\\Fonts\\arial.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
    ];
    foreach ($candidates as $font) {
        if (is_file($font)) {
            return $font;
        }
    }

    return $candidates[0];
}

$specs = [
    ['icon-192.png', 192, 'ea580c', null, null],
    ['icon-512.png', 512, 'ea580c', null, null],
    ['apple-touch-icon.png', 180, 'ea580c', null, null],
    ['vendor-192.png', 192, 'ea580c', '9a3412', 'Satıcı'],
    ['vendor-512.png', 512, 'ea580c', '9a3412', 'Satıcı'],
    ['outdoor-192.png', 192, '059669', '064e3b', 'Outdoor'],
    ['outdoor-512.png', 512, '059669', '064e3b', 'Outdoor'],
    ['saha-192.png', 192, '1e3a5f', '0f172a', 'Saha'],
    ['saha-512.png', 512, '1e3a5f', '0f172a', 'Saha'],
];

foreach ($specs as [$file, $size, $bg, $banner, $label]) {
    paintIcon($iconsDir.DIRECTORY_SEPARATOR.$file, $size, $bg, $banner, $label);
    echo $file.PHP_EOL;
}

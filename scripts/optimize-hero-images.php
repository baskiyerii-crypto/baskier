<?php

$src = __DIR__ . '/../public/images/home/hero-slide-1.png';
if (!file_exists($src)) {
    echo "Source not found\n";
    exit(1);
}

$img = imagecreatefromstring(file_get_contents($src));
$w = imagesx($img);
$h = imagesy($img);

$destDir = __DIR__ . '/../public/images/home/';

// Desktop (original size, e.g. 1280x720)
imagewebp($img, $destDir . 'hero-desktop.webp', 82);
if (function_exists('imageavif')) {
    imageavif($img, $destDir . 'hero-desktop.avif', 75);
}

// Mobile (720px width)
$mw = 720;
$mh = (int) round($h * ($mw / $w));
$mimg = imagecreatetruecolor($mw, $mh);
imagecopyresampled($mimg, $img, 0, 0, 0, 0, $mw, $mh, $w, $h);

imagewebp($mimg, $destDir . 'hero-mobile.webp', 80);
if (function_exists('imageavif')) {
    imageavif($mimg, $destDir . 'hero-mobile.avif', 72);
}

echo "Desktop WebP: " . filesize($destDir . 'hero-desktop.webp') . " bytes\n";
if (file_exists($destDir . 'hero-desktop.avif')) {
    echo "Desktop AVIF: " . filesize($destDir . 'hero-desktop.avif') . " bytes\n";
}
echo "Mobile WebP: " . filesize($destDir . 'hero-mobile.webp') . " bytes\n";
if (file_exists($destDir . 'hero-mobile.avif')) {
    echo "Mobile AVIF: " . filesize($destDir . 'hero-mobile.avif') . " bytes\n";
}

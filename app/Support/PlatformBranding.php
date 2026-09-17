<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class PlatformBranding
{
    public static function logoRelativePath(): ?string
    {
        $path = Setting::get('platform_logo');
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'uploads/')) {
            if (File::exists(public_path($path))) {
                return $path;
            }
            $mirrored = ltrim(substr($path, strlen('uploads/')), '/');
            if ($mirrored && File::exists(storage_path('app/public/'.$mirrored))) {
                return 'storage/'.$mirrored;
            }

            return null;
        }

        if (File::exists(public_path('storage/'.$path))) {
            return 'storage/'.$path;
        }
        if (File::exists(storage_path('app/public/'.$path))) {
            return 'storage/'.$path;
        }
        if (File::exists(public_path('uploads/'.$path))) {
            return 'uploads/'.$path;
        }

        return null;
    }

    public static function logoUrl(): ?string
    {
        $rel = self::logoRelativePath();

        return $rel ? asset($rel) : null;
    }

    public static function storeLogo(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'], true)) {
            $ext = 'png';
        }
        $filename = 'logo.'.$ext;

        Storage::disk('public')->makeDirectory('branding');
        foreach (Storage::disk('public')->files('branding') as $existing) {
            if (str_starts_with(basename($existing), 'logo.')) {
                Storage::disk('public')->delete($existing);
            }
        }
        Storage::disk('public')->putFileAs('branding', $file, $filename);

        try {
            $uploadDir = public_path('uploads/branding');
            File::ensureDirectoryExists($uploadDir);
            foreach (File::files($uploadDir) as $existing) {
                if (str_starts_with($existing->getFilename(), 'logo.')) {
                    File::delete($existing->getPathname());
                }
            }
            File::copy(storage_path('app/public/branding/'.$filename), $uploadDir.DIRECTORY_SEPARATOR.$filename);
        } catch (\Throwable) {
            // public/uploads yazılamazsa storage disk yeterli (Coolify volume)
        }

        $relative = 'uploads/branding/'.$filename;
        Setting::set('platform_logo', $relative);
        self::generatePwaIcons();

        return $relative;
    }

    public static function generatePwaIcons(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $dir = public_path('icons');
        File::ensureDirectoryExists($dir);
        $name = SiteMenu::platformName();
        $logoPath = self::absoluteLogoPath();

        foreach ([192, 512] as $size) {
            $im = imagecreatetruecolor($size, $size);
            if ($im === false) {
                continue;
            }
            $white = imagecolorallocate($im, 255, 255, 255);
            $slate = imagecolorallocate($im, 15, 23, 42);
            $orange = imagecolorallocate($im, 234, 88, 12);
            imagefilledrectangle($im, 0, 0, $size, $size, $white);

            $logoH = (int) round($size * 0.52);
            $logoY = (int) round($size * 0.12);
            $drawn = false;
            if ($logoPath) {
                $src = self::loadGdImage($logoPath);
                if ($src) {
                    $sw = imagesx($src);
                    $sh = imagesy($src);
                    if ($sw > 0 && $sh > 0) {
                        $scale = min($logoH / $sh, ($size * 0.72) / $sw);
                        $dw = max(1, (int) round($sw * $scale));
                        $dh = max(1, (int) round($sh * $scale));
                        $dx = (int) (($size - $dw) / 2);
                        imagecopyresampled($im, $src, $dx, $logoY, 0, 0, $dw, $dh, $sw, $sh);
                        imagedestroy($src);
                        $drawn = true;
                    }
                }
            }
            if (! $drawn) {
                $box = (int) round($size * 0.38);
                $bx = (int) (($size - $box) / 2);
                imagefilledrectangle($im, $bx, $logoY, $bx + $box, $logoY + $box, $orange);
            }

            $font = (int) max(2, min(5, round($size / 64)));
            $text = mb_substr($name, 0, 18);
            $tw = imagefontwidth($font) * strlen($text);
            $tx = max(4, (int) (($size - $tw) / 2));
            $ty = (int) ($size * 0.78);
            imagestring($im, $font, $tx, $ty, $text, $slate);

            imagepng($im, $dir.DIRECTORY_SEPARATOR.'icon-'.$size.'.png');
            imagedestroy($im);
        }

        if (File::exists($dir.'/icon-192.png')) {
            File::copy($dir.'/icon-192.png', $dir.'/apple-touch-icon.png');
        }
    }

    private static function absoluteLogoPath(): ?string
    {
        $rel = self::logoRelativePath();
        if (! $rel) {
            return null;
        }
        if (str_starts_with($rel, 'storage/')) {
            $abs = storage_path('app/public/'.substr($rel, strlen('storage/')));
            if (is_file($abs)) {
                return $abs;
            }
        }
        $public = public_path($rel);
        if (is_file($public)) {
            return $public;
        }

        return null;
    }

    private static function loadGdImage(string $path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png' => @imagecreatefrompng($path),
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'gif' => @imagecreatefromgif($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }
}

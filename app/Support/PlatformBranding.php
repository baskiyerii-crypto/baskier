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

        return $relative;
    }
}

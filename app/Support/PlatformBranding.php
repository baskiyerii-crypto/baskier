<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

final class PlatformBranding
{
    public static function logoRelativePath(): ?string
    {
        $path = Setting::get('platform_logo');
        if (! $path) {
            return null;
        }

        // Prefer public uploads (survives storage:link issues).
        if (str_starts_with($path, 'uploads/')) {
            return File::exists(public_path($path)) ? $path : null;
        }

        // Legacy storage/app/public path
        if (File::exists(public_path('storage/'.$path))) {
            return 'storage/'.$path;
        }
        if (File::exists(storage_path('app/public/'.$path))) {
            return 'storage/'.$path;
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
        $dir = public_path('uploads/branding');
        File::ensureDirectoryExists($dir);

        foreach (File::files($dir) as $existing) {
            if (str_starts_with($existing->getFilename(), 'logo.')) {
                File::delete($existing->getPathname());
            }
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        $relative = 'uploads/branding/logo.'.$ext;
        $file->move($dir, 'logo.'.$ext);

        Setting::set('platform_logo', $relative);

        return $relative;
    }
}

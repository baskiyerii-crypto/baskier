<?php

namespace App\Support;

final class MediaUrl
{
    public static function public(null|string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = ltrim((string) $path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'uploads/') && is_file(public_path($path))) {
            return asset($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if (is_file(public_path('storage/'.$path))) {
            return asset('storage/'.$path);
        }

        if (is_file(storage_path('app/public/'.$path))) {
            return asset('storage/'.$path);
        }

        return asset('storage/'.$path);
    }
}

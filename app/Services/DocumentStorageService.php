<?php

namespace App\Services;

use App\Models\VendorDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentStorageService
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB

    /**
     * Validate and securely store an uploaded document onto the private disk.
     *
     * @return array{
     *     disk: string,
     *     file_path: string,
     *     original_filename: string,
     *     mime_type: string,
     *     file_size: int,
     *     quarantine_status: string
     * }
     */
    public function storeUploadedDocument(UploadedFile $file, int $vendorId): array
    {
        $fileSize = $file->getSize();
        if ($fileSize > self::MAX_FILE_SIZE_BYTES) {
            throw new InvalidArgumentException(__('panel.file_too_large', ['max' => '10 MB']) ?: 'Dosya boyutu 10 MB sınırını aşamaz.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Yalnızca PDF, JPG, PNG ve WEBP formatları desteklenmektedir.');
        }

        // Real MIME type detection from actual file binary contents
        $realMime = $this->detectRealMimeType($file);
        if (! in_array($realMime, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException('Geçersiz veya güvenlik riski taşıyan dosya türü tespit edildi.');
        }

        // Generate safe unique filename
        $safeFileName = Str::random(40).'.'.$extension;
        $directory = 'vendor-documents/'.$vendorId;
        $filePath = $directory.'/'.$safeFileName;

        // Store to configured private disk
        $diskName = config('filesystems.private_disk', 'private');
        $stored = Storage::disk($diskName)->putFileAs($directory, $file, $safeFileName);
        if (! $stored) {
            throw new \RuntimeException('Dosya özel depolama alanına kaydedilemedi.');
        }

        return [
            'disk' => $diskName,
            'file_path' => $filePath,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $realMime,
            'file_size' => $fileSize,
            'quarantine_status' => 'clean',
        ];
    }

    /**
     * Generate a short-lived (e.g. 10 minutes) temporary signed download URL.
     */
    public function generateTemporaryDownloadUrl(VendorDocument $document, int $minutes = 10): string
    {
        return URL::temporarySignedRoute(
            'vendor.documents.download',
            now()->addMinutes($minutes),
            ['document' => $document->id]
        );
    }

    /**
     * Stream or download the file response with dual-read fallback support.
     */
    public function getFileResponse(VendorDocument $document): StreamedResponse
    {
        if ($document->quarantine_status && $document->quarantine_status !== 'clean') {
            abort(403, 'Bu dosya güvenlik incelemesi / karantina altındadır.');
        }

        $targetPath = $document->file_path ?: $document->path;
        $disk = $document->disk ?: 'private';

        // 1. Primary read from assigned disk
        if (Storage::disk($disk)->exists($targetPath)) {
            return Storage::disk($disk)->response(
                $targetPath,
                $document->original_filename ?: basename($targetPath)
            );
        }

        // 2. Dual-read fallback to public disk for unmigrated legacy files
        if (Storage::disk('public')->exists($document->path ?: $targetPath)) {
            return Storage::disk('public')->response(
                $document->path ?: $targetPath,
                $document->original_filename ?: basename($targetPath)
            );
        }

        abort(404, 'Belge dosyası depolama alanında bulunamadı.');
    }

    /**
     * Migrate a legacy document to private disk safely.
     */
    public function migrateDocumentToPrivate(VendorDocument $document): bool
    {
        $path = $document->path ?: $document->file_path;
        if (! $path) {
            return false;
        }

        // If already in private, ensure file_path and disk are set
        if ($document->disk === 'private' && Storage::disk('private')->exists($document->file_path ?: $path)) {
            if (empty($document->file_path)) {
                $document->update(['file_path' => $path]);
            }
            return true;
        }

        // Check if legacy file exists on public disk
        if (Storage::disk('public')->exists($path)) {
            $content = Storage::disk('public')->get($path);
            if ($content !== null) {
                Storage::disk('private')->put($path, $content);

                // Verify copy succeeded on private disk before updating model
                if (Storage::disk('private')->exists($path)) {
                    $mime = Storage::disk('private')->mimeType($path);
                    $size = Storage::disk('private')->size($path);

                    $document->update([
                        'disk' => 'private',
                        'file_path' => $path,
                        'mime_type' => $mime ?: $document->mime_type,
                        'file_size' => $size ?: $document->file_size,
                    ]);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect real MIME type using PHP fileinfo or mime_content_type.
     */
    protected function detectRealMimeType(UploadedFile $file): string
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath)) {
            return $file->getClientMimeType() ?: 'application/octet-stream';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $realPath);
            finfo_close($finfo);
            if ($mime && ! in_array($mime, ['application/x-empty', 'application/octet-stream'], true)) {
                return $mime;
            }
        }

        $clientMime = $file->getClientMimeType();
        if ($clientMime && in_array($clientMime, self::ALLOWED_MIME_TYPES, true)) {
            return $clientMime;
        }

        return @mime_content_type($realPath) ?: ($file->getClientMimeType() ?: 'application/octet-stream');
    }
}

<?php

namespace App\Services;

use App\Models\OohInventory;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class OutdoorInventoryBulkService
{
    public function __construct(
        private OutdoorInventoryService $inventories,
        private OutdoorStaffService $staff,
    ) {}

    /**
     * @return list<array<int, string|int|float|null>>
     */
    public function exportRows(Vendor $vendor, User $actor): array
    {
        $this->staff->assertCanManageInventory($actor, $vendor);
        $query = $vendor->oohInventories()->with('images')->orderBy('id');
        if (! $this->staff->isAccountOwner($actor, $vendor)) {
            $query->where('created_by_user_id', $actor->id);
        }

        $rows = [];
        foreach ($query->get() as $inv) {
            $files = $inv->images->map(fn ($img) => basename((string) $img->path))->filter()->implode(',');
            $rows[] = [
                $inv->id,
                $inv->title,
                $inv->category_id,
                $inv->description,
                $inv->country_code,
                $inv->city,
                $inv->district,
                $inv->address,
                $inv->lat,
                $inv->lng,
                $inv->permit_no,
                $inv->list_price,
                $inv->price_unit,
                $inv->face_width_m,
                $inv->face_height_m,
                $inv->facing,
                $inv->illuminated ? 1 : 0,
                $files,
                $inv->status,
            ];
        }

        return $rows;
    }

    /**
     * @return array{created:int,updated:int,errors:list<string>}
     */
    public function import(Vendor $vendor, User $actor, UploadedFile $xlsx, ?UploadedFile $zip = null): array
    {
        $this->staff->assertCanManageInventory($actor, $vendor);
        if ($this->staff->isFieldOperator($actor, $vendor)) {
            throw new RuntimeException('Saha personeli toplu envanter yükleyemez.');
        }

        $zipDir = null;
        $zipIndex = [];
        if ($zip) {
            [$zipDir, $zipIndex] = $this->extractZip($zip);
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        try {
            $rows = $this->readSpreadsheet($xlsx);
            foreach ($rows as $i => $row) {
                $line = $i + 2;
                try {
                    $result = $this->upsertRow($vendor, $actor, $row, $zipIndex);
                    if ($result === 'created') {
                        $created++;
                    } elseif ($result === 'updated') {
                        $updated++;
                    }
                } catch (RuntimeException $e) {
                    $errors[] = 'Satır '.$line.': '.$e->getMessage();
                } catch (\Throwable $e) {
                    $errors[] = 'Satır '.$line.': '.$e->getMessage();
                }
            }
        } finally {
            if ($zipDir && is_dir($zipDir)) {
                File::deleteDirectory($zipDir);
            }
        }

        return compact('created', 'updated', 'errors');
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, string>  $zipIndex basename => absolute path
     */
    private function upsertRow(Vendor $vendor, User $actor, array $row, array $zipIndex): ?string
    {
        $title = trim((string) ($row['title'] ?? ''));
        if ($title === '') {
            return null;
        }
        $categoryId = (int) ($row['category_id'] ?? 0);
        if ($categoryId < 1) {
            throw new RuntimeException('category_id gerekli.');
        }
        $lat = $row['lat'] ?? null;
        $lng = $row['lng'] ?? null;
        if ($lat === null || $lat === '' || $lng === null || $lng === '') {
            throw new RuntimeException('lat/lng gerekli.');
        }

        $payload = [
            'title' => $title,
            'category_id' => $categoryId,
            'description' => $row['description'] ?? null,
            'country_code' => strtoupper(trim((string) ($row['country_code'] ?? 'TR'))) ?: 'TR',
            'city' => $row['city'] ?? null,
            'district' => $row['district'] ?? null,
            'address' => $row['address'] ?? null,
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'permit_no' => $row['permit_no'] ?? null,
            'list_price' => isset($row['list_price']) && $row['list_price'] !== '' ? (float) $row['list_price'] : null,
            'price_unit' => in_array($row['price_unit'] ?? 'month', ['day', 'week', 'month'], true)
                ? $row['price_unit']
                : 'month',
            'face_width_m' => isset($row['face_width_m']) && $row['face_width_m'] !== '' ? (float) $row['face_width_m'] : null,
            'face_height_m' => isset($row['face_height_m']) && $row['face_height_m'] !== '' ? (float) $row['face_height_m'] : null,
            'facing' => in_array($row['facing'] ?? '', ['N', 'E', 'S', 'W'], true) ? $row['facing'] : null,
            'illuminated' => filter_var($row['illuminated'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'proof_radius_m' => 75,
        ];

        $id = (int) ($row['id'] ?? 0);
        $inventory = null;
        $action = null;
        if ($id > 0) {
            $inventory = OohInventory::query()
                ->whereKey($id)
                ->where('vendor_id', $vendor->id)
                ->first();
            if (! $inventory) {
                throw new RuntimeException('id #'.$id.' bu satıcıya ait değil.');
            }
            if (! $this->staff->isAccountOwner($actor, $vendor)
                && (int) $inventory->created_by_user_id !== (int) $actor->id) {
                throw new RuntimeException('Bu panoyu güncelleme yetkiniz yok.');
            }
            $this->inventories->update($inventory, $actor, $payload, []);
            $action = 'updated';
            $inventory = $inventory->fresh();
        } else {
            $inventory = $this->inventories->create($vendor, $actor, $payload, []);
            $action = 'created';
        }

        $imageFiles = trim((string) ($row['image_files'] ?? ''));
        if ($imageFiles !== '' && $zipIndex !== []) {
            $names = array_values(array_filter(array_map('trim', explode(',', $imageFiles))));
            $uploads = [];
            foreach ($names as $name) {
                $base = basename($name);
                $path = $zipIndex[$base]
                    ?? $zipIndex[$name]
                    ?? $zipIndex[$inventory->id.'/'.$base]
                    ?? null;
                if (! $path || ! is_file($path)) {
                    continue;
                }
                $uploads[] = new UploadedFile($path, $base, mime_content_type($path) ?: 'image/jpeg', null, true);
            }
            if ($uploads !== []) {
                $this->inventories->replaceImages($inventory, $actor, $uploads);
            }
        }

        return $action;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readSpreadsheet(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        if (in_array($ext, ['csv', 'txt'], true)) {
            return $this->readCsv($file);
        }

        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new class {}, $file);
        $sheet = $sheets[0] ?? [];
        if ($sheet === []) {
            return [];
        }
        $header = array_map(fn ($h) => Str::snake(strtolower(trim((string) $h))), array_shift($sheet) ?? []);
        $rows = [];
        foreach ($sheet as $raw) {
            if (! is_array($raw)) {
                continue;
            }
            $assoc = [];
            foreach ($header as $i => $key) {
                if ($key === '') {
                    continue;
                }
                $assoc[$key] = $raw[$i] ?? null;
            }
            if (trim((string) ($assoc['title'] ?? '')) === '') {
                continue;
            }
            $rows[] = $assoc;
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return [];
        }
        $header = null;
        $rows = [];
        while (($raw = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = array_map(fn ($h) => Str::snake(strtolower(trim((string) $h))), $raw);

                continue;
            }
            $assoc = [];
            foreach ($header as $i => $key) {
                if ($key === '') {
                    continue;
                }
                $assoc[$key] = $raw[$i] ?? null;
            }
            if (trim((string) ($assoc['title'] ?? '')) === '') {
                continue;
            }
            $rows[] = $assoc;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private function extractZip(UploadedFile $zip): array
    {
        $dir = storage_path('app/tmp/ooh-import-'.Str::lower(Str::random(10)));
        File::ensureDirectoryExists($dir);
        $archive = new ZipArchive;
        if ($archive->open($zip->getRealPath()) !== true) {
            throw new RuntimeException('ZIP açılamadı.');
        }
        $archive->extractTo($dir);
        $archive->close();

        $index = [];
        foreach (File::allFiles($dir) as $file) {
            $rel = str_replace('\\', '/', $file->getRelativePathname());
            $index[$file->getFilename()] = $file->getPathname();
            $index[$rel] = $file->getPathname();
        }

        return [$dir, $index];
    }
}

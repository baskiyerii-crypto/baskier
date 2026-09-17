<?php

namespace App\Console\Commands;

use App\Models\VendorDocument;
use App\Services\DocumentStorageService;
use Illuminate\Console\Command;

class MigrateVendorDocumentsToPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:migrate-to-private';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy public vendor documents to private storage disk with dual-read safety';

    /**
     * Execute the console command.
     */
    public function handle(DocumentStorageService $storageService): int
    {
        $documents = VendorDocument::all();
        $this->info("Toplam {$documents->count()} adet satıcı belgesi inceleniyor...");

        $migrated = 0;
        $alreadyPrivate = 0;
        $missing = 0;

        foreach ($documents as $doc) {
            if ($doc->disk === 'private' && ! empty($doc->file_path)) {
                $alreadyPrivate++;
                continue;
            }

            $success = $storageService->migrateDocumentToPrivate($doc);
            if ($success) {
                $migrated++;
            } else {
                $missing++;
                $this->warn("Belge #{$doc->id} (Dosya: {$doc->path}) taşınamadı veya bulunamadı.");
            }
        }

        $this->info("Taşıma tamamlandı: {$migrated} taşındı, {$alreadyPrivate} zaten özel alanda, {$missing} eksik/bulunamadı.");

        return Command::SUCCESS;
    }
}

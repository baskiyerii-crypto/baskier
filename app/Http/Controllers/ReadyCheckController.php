<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReadyCheckController extends Controller
{
    /**
     * Readiness probe for Kubernetes, Docker Swarm, Coolify, and Load Balancers.
     *
     * Validates Database, Cache, Queue, Storage, and Database Migrations.
     * Never exposes credentials, table names, or raw error messages in HTTP body.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $failures = [];

        // 1. Database
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $failures['database'] = $e->getMessage();
        }

        // 2. Cache
        try {
            $cacheKey = 'ready_probe_' . Str::random(12);
            Cache::put($cacheKey, 1, 10);
            if (Cache::get($cacheKey) !== 1) {
                $failures['cache'] = 'Cache readback failed';
            }
            Cache::forget($cacheKey);
        } catch (\Throwable $e) {
            $failures['cache'] = $e->getMessage();
        }

        // 3. Queue
        try {
            $queueConnection = Queue::connection();
            if ($queueConnection === null) {
                $failures['queue'] = 'Queue connection unavailable';
            }
        } catch (\Throwable $e) {
            $failures['queue'] = $e->getMessage();
        }

        // 4. Storage
        try {
            $diskName = config('filesystems.private_disk', 'private');
            $disk = Storage::disk($diskName);
            $probeFile = 'probe_' . Str::random(12) . '.tmp';
            $disk->put($probeFile, 'ready');
            if (! $disk->exists($probeFile)) {
                $failures['storage'] = 'Storage probe file could not be verified';
            }
            $disk->delete($probeFile);
        } catch (\Throwable $e) {
            $failures['storage'] = $e->getMessage();
        }

        // 5. Migrations
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
            $ran = $migrator->getRepository()->getRan();
            $pending = array_diff(array_keys($files), $ran);

            if (! empty($pending)) {
                $failures['migrations'] = count($pending) . ' pending migration(s)';
            }
        } catch (\Throwable $e) {
            $failures['migrations'] = $e->getMessage();
        }

        if (! empty($failures)) {
            Log::warning('Readiness probe failed', [
                'components' => array_keys($failures),
                'details'    => $failures,
                'correlation_id' => $request->header('X-Correlation-ID') ?? $request->attributes->get('correlation_id'),
            ]);

            return response()->json([
                'status' => 'not_ready',
            ], 503);
        }

        return response()->json([
            'status' => 'ready',
        ], 200);
    }
}
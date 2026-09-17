<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\App\Http\Middleware\CaptureCorrelationId::class);
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\EnsurePendingReviewPrompt::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'odeme/iyzico/callback',
            'odeme/iyzico/webhook',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'vendor.not_suspended' => \App\Http\Middleware\EnsureVendorNotSuspended::class,
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->command('contracts:suspend-overdue')->hourly();
        $schedule->command('platform:backup')->dailyAt('03:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() < 500) {
                return;
            }
            \Illuminate\Support\Facades\Log::critical('5xx_server_error', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'correlation_id' => request()->header('X-Correlation-ID') ?? request()->attributes->get('correlation_id'),
            ]);
        });
    })->create();

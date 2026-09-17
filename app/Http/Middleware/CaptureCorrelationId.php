<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CaptureCorrelationId
{
    public const HEADER_NAME = 'X-Correlation-ID';
    public const ALT_HEADER_NAME = 'X-Request-ID';

    /**
     * Handle an incoming request and attach an end-to-end correlation ID.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header(self::HEADER_NAME)
            ?: $request->header(self::ALT_HEADER_NAME);

        if (empty($correlationId) || ! is_string($correlationId) || strlen($correlationId) > 64) {
            $correlationId = (string) Str::uuid();
        }

        $request->headers->set(self::HEADER_NAME, $correlationId);
        $request->attributes->set('correlation_id', $correlationId);

        // Share with Monolog / Laravel Log context across this entire request lifecycle
        Log::shareContext([
            'correlation_id' => $correlationId,
        ]);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set(self::HEADER_NAME, $correlationId);

        return $response;
    }
}
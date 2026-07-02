<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    use AuthorizesRequests;

    protected function ok(mixed $data = null, ?string $message = null, mixed $meta = null, int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $meta, $status);
    }

    protected function fail(string $message, mixed $errors = null, int $status = 400, mixed $meta = null): JsonResponse
    {
        return ApiResponse::error($message, $errors, $status, $meta);
    }
}

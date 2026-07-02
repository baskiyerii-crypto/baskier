<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, mixed $meta = null, int $status = 200): JsonResponse
    {
        if ($data instanceof LengthAwarePaginator) {
            $paginatorMeta = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ];
            $meta = $meta !== null && $meta !== [] ? array_merge((array) $meta, $paginatorMeta) : $paginatorMeta;
            $data = $data->items();
        }

        return response()->json([
            'success' => true,
            'message' => $message ?? '',
            'data' => $data,
            'meta' => $meta,
            'errors' => null,
        ], $status);
    }

    public static function error(string $message, mixed $errors = null, int $status = 400, mixed $meta = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => $meta,
            'errors' => $errors,
        ], $status);
    }

    public static function fromException(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        if ($e instanceof ValidationException) {
            return self::error($e->getMessage(), $e->errors(), 422);
        }

        if ($e instanceof AuthenticationException) {
            return self::error('Oturum gerekli.', null, 401);
        }

        if ($e instanceof AuthorizationException) {
            return self::error('Bu işlem için yetkiniz yok.', null, 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return self::error('Kayıt bulunamadı.', null, 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return self::error('Uç nokta veya kayıt bulunamadı.', null, 404);
        }

        if ($e instanceof HttpExceptionInterface) {
            $msg = $e->getMessage() ?: 'İstek işlenemedi.';

            return self::error($msg, null, $e->getStatusCode());
        }

        if (app()->hasDebugModeEnabled()) {
            return self::error($e->getMessage(), ['exception' => get_class($e)], 500);
        }

        return self::error('Sunucu hatası.', null, 500);
    }
}

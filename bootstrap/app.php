<?php

use App\Http\Middleware\EnsureMemberProfileIsComplete;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/midtrans',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'member.profile.complete' => EnsureMemberProfileIsComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('gymmi/chat', 'member/gymmi/chat') || ! $request->expectsJson()) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            $status = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof TokenMismatchException => 419,
                $exception instanceof AuthenticationException => 401,
                default => $status,
            };
            $code = match ($status) {
                401, 403 => 'unauthorized',
                419 => 'session_expired',
                422 => 'validation_failed',
                429 => 'rate_limited',
                503 => 'temporary_unavailable',
                default => 'temporary_unavailable',
            };
            $message = match ($code) {
                'unauthorized' => 'Akses Gymmi member memerlukan akun member yang valid.',
                'session_expired' => 'Sesi halaman berakhir. Muat ulang halaman lalu coba lagi.',
                'validation_failed' => 'Pesan belum dapat diproses. Periksa isi pesan lalu coba lagi.',
                'rate_limited' => 'Permintaan terlalu sering. Tunggu sebentar lalu coba lagi.',
                default => 'Gymmi sedang mengalami gangguan sementara. Silakan coba lagi.',
            };
            $retryable = in_array($status, [429, 500, 502, 503, 504], true);

            $payload = [
                'status' => 'error',
                'code' => $code,
                'message' => $message,
                'retryable' => $retryable,
            ];

            if ($exception instanceof ValidationException) {
                $payload['errors'] = $exception->errors();
            }

            return response()->json($payload, $status);
        });
    })->create();

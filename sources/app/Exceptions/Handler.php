<?php

namespace App\Exceptions;

use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Support\Facades\View;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response|null
    {
        // Handle 403 (Akses ditolak)
        if ($e instanceof UnauthorizedException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
            }
            return $this->renderErrorView($e, 403, 'Akses Ditolak', 'Maaf, Anda tidak diizinkan mengakses ke halaman ini.');
        }

        // Handle 404 (Halaman tidak ditemukan)
        if ($e instanceof NotFoundHttpException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Halaman tidak ditemukan.'], 404);
            }
            return $this->renderErrorView($e, 404, 'Halaman Tidak Ditemukan', 'Halaman yang Anda cari tidak ditemukan atau telah dipindahkan.');
        }

        // Handle Lain-lain (Manual Abort 403, 503, dll)
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();

            // Kita cuma handle error tampilan kalau view-nya ada
            if (View::exists('system::errors.index')) {
                // Judul default sesuai status code
                $title = match($statusCode) {
                    403 => 'Akses Ditolak',
                    503 => 'Sedang Pemeliharaan',
                    default => 'Terjadi Kesalahan',
                };

                return $this->renderErrorView($e, $statusCode, $title, $e->getMessage());
            }
        }

        // Sisanya biarkan Laravel (Termasuk Error 500 Crash Kodingan)
        return parent::render($request, $e);
    }

    private function renderErrorView($e, $code, $title, $message = null): ?\Illuminate\Http\Response
    {
        if (View::exists('system::errors.index')) {
            return response()->view('system::errors.index', [
                'title' => $title,
                'message' => $message ?: $e->getMessage(),
                'code' => $code,
                'exception' => $e
            ], $code);
        }

        return null;
    }
}

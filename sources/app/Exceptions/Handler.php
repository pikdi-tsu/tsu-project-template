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
        // 403 Alert
        if (($e instanceof HttpException && $e->getStatusCode() === 403) || $e instanceof UnauthorizedException) {

            $defaultMessage = 'Akses Dibatasi! Anda tidak memiliki izin untuk aksi ini.';
            $message = $defaultMessage;

            // Middleware Spatie (registerPermissions)
            if ($e instanceof UnauthorizedException) {
                $requiredPermissions = $e->getRequiredPermissions();
                $cleanActions = [];

                foreach ($requiredPermissions as $perm) {
                    $array = explode(':', $perm);
                    $cleanActions[] = ucfirst(end($array));
                }

                if (!empty($cleanActions)) {
                    $message = 'Akses Dibatasi! Anda tidak memiliki izin: ' . implode(', ', $cleanActions);
                }
            }
            // Middleware Manual
            else if (!empty($e->getMessage())) {
                $message = $e->getMessage();
            }

            // RESPON: JSON (AJAX) ATAU REDIRECT BACK (SWEETALERT)
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 403);
            }

            return redirect()->back()->with('error', $message);
        }

        // Handle 403 (Akses ditolak)
//        if ($e instanceof UnauthorizedException) {
//            if ($request->expectsJson()) {
//                return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
//            }
//            return $this->renderErrorView($e, 403, 'Akses Ditolak', 'Maaf, Anda tidak diizinkan mengakses ke halaman ini.');
//        }

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
            if (View::exists('system::errors.index') && $statusCode !== 403) {
                $title = match($statusCode) {
                    503 => 'Sedang Pemeliharaan',
                    500 => 'Terjadi Kesalahan Server',
                    419 => 'Sesi Kadaluarsa',
                    429 => 'Terlalu Banyak Permintaan',
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

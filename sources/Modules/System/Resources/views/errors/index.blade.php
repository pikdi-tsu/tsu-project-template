@extends('system::errors.layouts.layout')

@php
    $incomingCode = $code ?? null;
    $incomingException = $exception ?? null;
    $incomingMessage = $message ?? null;
    $incomingTitle = $title ?? null;

    // Tentukan STATUS CODE (Prioritas: Controller -> Exception -> Default 500)
    if ($incomingCode) {
        $finalCode = (int) $incomingCode;
    } elseif ($incomingException && method_exists($incomingException, 'getStatusCode')) {
        $finalCode = $incomingException->getStatusCode();
    } else if ($incomingException instanceof \Illuminate\Auth\AuthenticationException) {
            $finalCode = 401;
        } else {
            $finalCode = 500;
        }

    // Title Default berdasarkan Code
    $defaultTitle = match($finalCode) {
        401 => 'Belum Login',
        403 => 'Akses Ditolak',
        404 => 'Halaman Tidak Ditemukan',
        419 => 'Sesi Kadaluarsa',
        429 => 'Terlalu Banyak Request',
        500 => 'Server Error',
        503 => 'Sedang Pemeliharaan',
        default => 'Terjadi Kesalahan',
    };

    // Pesan Default berdasarkan Code
    $defaultMessage = match($finalCode) {
        401 => 'Sesi Anda telah berakhir atau Anda belum login. Silakan login ulang.',
        403 => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
        404 => 'Halaman yang Anda cari tidak ditemukan atau telah dipindahkan.',
        419 => 'Halaman telah kadaluarsa. Silakan refresh browser Anda.',
        500 => 'Terjadi kesalahan internal pada server kami.',
        503 => 'Sistem sedang dalam perbaikan rutin. Silakan coba sesaat lagi.',
        default => 'Terjadi kesalahan teknis yang tidak diketahui.',
    };

    $titleToShow = $incomingTitle ?? $defaultTitle;

    // Khusus mode debug false
    if ($finalCode === 500 && !config('app.debug')) {
        $messageToShow = $incomingMessage ?? $defaultMessage;
    } else {
        // Ambil pesan exception jika ada
        $exMsg = $incomingException ? $incomingException->getMessage() : null;
        $messageToShow = $incomingMessage ?? ($exMsg ?: $defaultMessage);
    }
@endphp

@section('title', $titleToShow)
@section('code', $finalCode)

@section('message')
    {{ $messageToShow }}
@endsection

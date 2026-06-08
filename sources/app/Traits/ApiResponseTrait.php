<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Mengembalikan response JSON standar yang terpusat di seluruh sistem.
     * Mengikuti Rule #10: success, message, data, errors.
     *
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @param mixed $errors
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResponse(bool $success, string $message = '', $data = null, $errors = null, int $statusCode = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors,
        ], $statusCode);
    }

    /**
     * Response untuk permintaan yang berhasil (200 OK)
     */
    protected function sendSuccess(string $message = 'Data berhasil diproses.', $data = null, int $statusCode = 200)
    {
        return $this->sendResponse(true, $message, $data, null, $statusCode);
    }

    /**
     * Response untuk permintaan yang gagal/error (contoh 400 Bad Request, atau 422 Unprocessable)
     */
    protected function sendError(string $message = 'Terjadi kesalahan pada sistem.', $errors = null, int $statusCode = 400)
    {
        return $this->sendResponse(false, $message, null, $errors, $statusCode);
    }
}

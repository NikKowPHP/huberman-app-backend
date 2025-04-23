<?php

namespace App\Modules\CoreApi\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseHelpers
{
    /**
     * Return a success JSON response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int|null  $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success(mixed $data, string $message = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $status
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message = null, int $status = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}

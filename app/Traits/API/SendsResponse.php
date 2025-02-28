<?php

declare(strict_types=1);

namespace App\Traits\API;

use Illuminate\Http\JsonResponse;

trait SendsResponse
{
    /**
     * Return success response.
     *
     * @param $message
     * @param $result
     *
     * @return JsonResponse
     */
    public function sendResponse($message, $result = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!empty($result)) {
            $response['data'] = $result;
        }

        return response()->json($response, 200);
    }

    /**
     * Return error response.
     *
     * @param int   $code
     * @param       $error
     * @param array $errorMessages
     *
     * @return JsonResponse
     */
    public function sendError(int $code, string $error, array $errorMessages = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}

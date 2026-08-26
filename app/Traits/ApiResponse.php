<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * إرجاع استجابة نجاح موحدة (Success Response)
     */
    public function successResponse($data = [], ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message ?? __('messages.operationSuccessful'),
            'data'    => $data ?? [],
        ], $code);
    }

    /**
     * إرجاع استجابة خطأ موحدة (Error Response)
     */
    public function errorResponse(?string $message = null, int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message ?? __('messages.errorOccurred'),
            'data'    => [],
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * استجابة عدم العثور على العنصر (404 Not Found)
     */
    public function notFoundResponse(?string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('messages.notFound'), 404);
    }

    /**
     * استجابة أخطاء التحقق من البيانات (422 Validation Error)
     */
    public function validationErrorResponse($errors, ?string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? __('messages.validationError'), 422, $errors);
    }
}

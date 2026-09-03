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
     * Return a paginated JSON response without manual links/meta.
     *
     * @param string $resource
     * @param mixed $data
     * @param string|null $message
     * @param array $extra
     * @return JsonResponse
     */
    public function paginated($resource, $data, ?string $message = null, array $extra = []): JsonResponse
    {
        $response = [
            'status'     => true,
            'message'    => $message ?? __('messages.operationSuccessful'),
            'data'       => $resource::collection($data->items()),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'last_page'    => $data->lastPage(),
            ],
        ];

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        return response()->json($response);
    }

    public function paginatedResponse($resource, $data, ?string $message = null, array $extra = []): JsonResponse
    {
        return $this->paginated($resource, $data, $message, $extra);
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

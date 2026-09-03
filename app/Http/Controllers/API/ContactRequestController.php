<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ContactRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactRequestController extends Controller
{
    protected ContactRequestService $contactRequestService;

    public function __construct(ContactRequestService $contactRequestService)
    {
        $this->contactRequestService = $contactRequestService;
    }

    /**
     * إرسال طلب تواصل من الشركة للباحث عن عمل
     */
    public function store(Request $request, string $id): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user();
        if (!$authUser) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.unauthenticated'),
                'data'    => [],
            ], 401);
        }

        $notes = $request->input('notes');

        return $this->contactRequestService->sendContactRequest($authUser, $id, $notes);
    }

    /**
     * عرض قائمة طلبات التواصل الخاصة بالشركة
     */
    public function companyRequests(Request $request): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user();
        if (!$authUser) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.unauthenticated'),
                'data'    => [],
            ], 401);
        }

        return $this->contactRequestService->getCompanyRequests($authUser, $request);
    }

    /**
     * عرض قائمة طلبات التواصل الواردة للباحث عن عمل
     */
    public function candidateRequests(Request $request): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user();
        if (!$authUser) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.unauthenticated'),
                'data'    => [],
            ], 401);
        }

        return $this->contactRequestService->getCandidateRequests($authUser, $request);
    }
}

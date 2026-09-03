<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\JobSeekerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobSeekerController extends Controller
{
    protected JobSeekerService $jobSeekerService;

    public function __construct(JobSeekerService $jobSeekerService)
    {
        $this->jobSeekerService = $jobSeekerService;
    }

    /**
     * Display a general list of job seekers (Card Resource view).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->jobSeekerService->getJobSeekers($request);
    }

    /**
     * Display inner detailed candidate profile by User ID or Profile ID.
     */
    public function show(string $id): JsonResponse
    {
        return $this->jobSeekerService->getJobSeeker($id);
    }

    /**
     * Toggle bookmark for a job seeker candidate.
     */
    public function toggleBookmark(string $id): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user();
        if (!$authUser) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.unauthenticated'),
                'data'    => [],
            ], 401);
        }

        return $this->jobSeekerService->toggleBookmark($authUser, $id);
    }

    /**
     * Display list of candidates bookmarked by authenticated user.
     */
    public function bookmarkedList(Request $request): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user();
        if (!$authUser) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.unauthenticated'),
                'data'    => [],
            ], 401);
        }

        return $this->jobSeekerService->getBookmarkedJobSeekers($authUser, $request);
    }
}

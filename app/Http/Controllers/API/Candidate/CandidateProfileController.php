<?php

namespace App\Http\Controllers\API\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Candidate\UpdateCandidateProfileRequest;
use App\Http\Requests\API\Candidate\UploadDocumentRequest;
use App\Http\Requests\API\Candidate\UploadVideoRequest;
use App\Services\CandidateProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CandidateProfileController extends Controller
{
    protected CandidateProfileService $candidateProfileService;

    public function __construct(CandidateProfileService $candidateProfileService)
    {
        $this->candidateProfileService = $candidateProfileService;
    }

    /**
     * Get candidate profile details
     */
    public function show(): JsonResponse
    {
        return $this->candidateProfileService->getProfile(Auth::user());
    }

    /**
     * Update candidate profile details (Professional Data)
     */
    public function update(UpdateCandidateProfileRequest $request): JsonResponse
    {
        return $this->candidateProfileService->updateProfile(Auth::user(), $request->validated());
    }

    /**
     * Upload personal documents (personal_photo, national_id, passport, cv)
     */
    public function uploadDocument(UploadDocumentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        return $this->candidateProfileService->uploadDocument(
            Auth::user(),
            $validated['document_type'],
            $request->file('file')
        );
    }

    /**
     * Upload candidate intro video
     */
    public function uploadVideo(UploadVideoRequest $request): JsonResponse
    {
        $validated = $request->validated();
        return $this->candidateProfileService->uploadVideo(
            Auth::user(),
            $request->file('video'),
            $validated['duration_seconds'] ?? null
        );
    }
}

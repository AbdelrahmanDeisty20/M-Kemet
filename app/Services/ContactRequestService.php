<?php

namespace App\Services;

use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactRequestService
{
    use ApiResponse;

    /**
     * إرسال طلب تواصل / توظيف من شركة لباحث عن عمل
     */
    public function sendContactRequest(User $companyUser, string $candidateId, ?string $notes = null): JsonResponse
    {
        if (!$companyUser->isCompany()) {
            return $this->errorResponse(__('messages.companyOnly') ?? 'هذا الإجراء مخصص لحسابات الشركات فقط', 403);
        }

        $company = $companyUser->company;
        if (!$company) {
            return $this->errorResponse(__('messages.profileNotFound'), 404);
        }

        $candidate = User::where('user_type', 'candidate')
            ->where(function ($q) use ($candidateId) {
                $q->where('id', $candidateId)
                  ->orWhereHas('candidateProfile', function ($p) use ($candidateId) {
                      $p->where('id', $candidateId);
                  });
            })
            ->with('candidateProfile')
            ->first();

        if (!$candidate || !$candidate->candidateProfile) {
            return $this->notFoundResponse(__('messages.user_not_found'));
        }

        $candidateProfile = $candidate->candidateProfile;

        // Check if contact request already exists
        $existingApp = Application::where('company_id', $company->id)
            ->where('candidate_profile_id', $candidateProfile->id)
            ->first();

        if ($existingApp) {
            return $this->successResponse([
                'application'   => new ApplicationResource($existingApp->load(['company', 'candidateProfile'])),
                'already_sent'  => true,
            ], __('messages.contactRequestAlreadySent') ?? 'تم إرسال طلب التواصل لهذا الباحث عن العمل مسبقاً');
        }

        $application = Application::create([
            'company_id'           => $company->id,
            'candidate_profile_id' => $candidateProfile->id,
            'status'               => 'pending',
            'notes'                => $notes,
        ]);

        ApplicationStatusHistory::create([
            'application_id'     => $application->id,
            'status'             => 'pending',
            'changed_by_user_id' => $companyUser->id,
            'notes'              => 'إرسال طلب تواصل جديد من الشركة',
        ]);

        return $this->successResponse([
            'application'  => new ApplicationResource($application->load(['company', 'candidateProfile'])),
            'already_sent' => false,
        ], __('messages.contactRequestSentSuccessfully') ?? 'تم إرسال طلب التواصل للباحث عن العمل بنجاح', 201);
    }

    /**
     * عرض طلبات التواصل المرسلة من الشركة
     */
    public function getCompanyRequests(User $companyUser, Request $request): JsonResponse
    {
        if (!$companyUser->isCompany()) {
            return $this->errorResponse(__('messages.companyOnly') ?? 'هذا الإجراء مخصص لحسابات الشركات فقط', 403);
        }

        $company = $companyUser->company;
        if (!$company) {
            return $this->errorResponse(__('messages.profileNotFound'), 404);
        }

        $perPage = (int) $request->input('per_page', 10);
        $applications = Application::where('company_id', $company->id)
            ->with([
                'company',
                'candidateProfile.user',
                'candidateProfile.currentCountry',
                'candidateProfile.profession',
                'candidateProfile.targetCountries',
                'candidateProfile.documents',
            ])
            ->latest()
            ->paginate($perPage);

        return $this->paginated(
            ApplicationResource::class,
            $applications,
            __('messages.operationSuccessful')
        );
    }

    /**
     * عرض طلبات التواصل الواردة للباحث عن عمل
     */
    public function getCandidateRequests(User $candidateUser, Request $request): JsonResponse
    {
        $profile = $candidateUser->candidateProfile;
        if (!$profile) {
            return $this->notFoundResponse(__('messages.profileNotFound'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $applications = Application::where('candidate_profile_id', $profile->id)
            ->with([
                'company.country',
                'candidateProfile',
            ])
            ->latest()
            ->paginate($perPage);

        return $this->paginated(
            ApplicationResource::class,
            $applications,
            __('messages.operationSuccessful')
        );
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class JobSeekerCardResource extends JsonResource
{
    /**
     * Transform the resource into an array (General / Card view).
     * Works on either User model (with candidateProfile loaded) or UserProfile model (with user loaded).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resolve User and UserProfile models
        $user = $this->resource instanceof \App\Models\User ? $this->resource : $this->user;
        $profile = $this->resource instanceof \App\Models\UserProfile ? $this->resource : $this->candidateProfile;

        // Check auth user bookmark status
        $authUser = Auth::guard('sanctum')->user() ?? $request->user();
        $isBookmarked = false;
        if ($authUser && $user) {
            $isBookmarked = $authUser->isCandidateBookmarked($user->id);
        }

        // Profile photo
        $personalPhotoDoc = $user?->documents?->where('document_type', 'personal_photo')->first();
        $profilePhotoUrl = $personalPhotoDoc?->secure_url;

        // Profession & Experience
        $professionModel = $profile?->profession;
        $professionTitle = $professionModel?->name ?? $profile?->sub_specialization ?? null;
        $experienceYears = $profile?->experience_years ?? 0;

        $professionWithExperience = null;
        if ($professionTitle) {
            $professionWithExperience = $experienceYears > 0 
                ? "{$professionTitle} | {$experienceYears} سنوات"
                : $professionTitle;
        }

        // Passport Status
        $passportDoc = $user?->documents?->where('document_type', 'passport')->first();
        $hasPassport = $passportDoc !== null;
        $passportStatusLabel = 'غير متوفر';
        if ($hasPassport) {
            $passportStatusLabel = $passportDoc->is_approved ? 'جواز ساري' : 'قيد المراجعة';
        }

        // Target countries
        $targetCountries = $profile?->targetCountries ? CountryResource::collection($profile->targetCountries) : [];
        $targetCountryNames = $profile?->targetCountries ? $profile->targetCountries->pluck('name')->toArray() : [];

        // Verification status
        $isVerified = ($profile?->status === 'approved') || !empty($user?->email_verified_at);
        $verificationBadgeLabel = $isVerified ? 'محقق' : 'غير محقق';

        return [
            'id'                       => $user?->id,
            'candidate_id'             => $user?->id,
            'user_id'                  => $user?->id,
            'profile_id'               => $profile?->id,
            'name'                     => $user?->name,
            'is_verified'              => $isVerified,
            'verification_badge'       => $verificationBadgeLabel,
            'profession_title'         => $professionTitle,
            'experience_years'         => $experienceYears,
            'profession_with_experience' => $professionWithExperience,
            'profile_photo'            => $profilePhotoUrl,
            'current_country'          => $profile?->currentCountry ? new CountryResource($profile->currentCountry) : null,
            'current_country_name'     => $profile?->currentCountry?->name,
            'passport_status'          => [
                'has_passport'  => $hasPassport,
                'is_approved'   => $passportDoc?->is_approved ?? false,
                'status_label'  => $passportStatusLabel,
            ],
            'passport_status_label'    => $passportStatusLabel,
            'target_countries'         => $targetCountries,
            'target_country_names'     => $targetCountryNames,
            'is_bookmarked'            => $isBookmarked,
        ];
    }
}

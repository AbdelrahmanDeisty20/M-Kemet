<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class JobSeekerDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array (Detailed view by ID).
     * Works on either User model (with relations) or UserProfile model.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resolve User and UserProfile models
        $user = $this->resource instanceof \App\Models\User ? $this->resource : $this->user;
        $profile = $this->resource instanceof \App\Models\UserProfile ? $this->resource : $this->candidateProfile;

        // Base card data
        $cardData = (new JobSeekerCardResource($user ?? $profile))->toArray($request);

        // Fetch document images (جميع الصور التي اترفعت في الوثائق)
        $allDocuments = $user?->documents ?? collect([]);
        
        $documentImages = $allDocuments->filter(function ($doc) {
            return $doc->is_image;
        })->map(function ($doc) {
            return [
                'id'               => $doc->id,
                'document_type'    => $doc->document_type,
                'document_type_name' => $doc->document_type_name,
                'url'              => $doc->secure_url,
                'is_approved'      => $doc->is_approved,
                'created_at'       => $doc->created_at?->toIso8601String(),
            ];
        })->values();

        // Intro video
        $videoResource = $user?->video ? new VideoResource($user->video) : null;

        return array_merge($cardData, [
            'email'                 => $user?->email,
            'phone'                 => $user?->phone,
            'birth_date'            => $profile?->birth_date?->format('Y-m-d'),
            'age'                   => $profile?->age,
            'gender'                => $profile?->genderRelation ? new GenderResource($profile->genderRelation) : null,
            'qualification'         => $profile?->qualificationRelation ? new QualificationResource($profile->qualificationRelation) : ($profile?->qualification ? new QualificationResource($profile->qualification) : null),
            'qualification_text'    => is_string($profile?->qualification) ? $profile->qualification : $profile?->qualificationRelation?->name,
            'sub_specialization'    => $profile?->sub_specialization,
            'experience_level'      => $profile?->experienceLevel ? new ExperienceLevelResource($profile->experienceLevel) : null,
            'expected_salary'       => $profile?->expected_salary,
            'willing_to_travel'     => $profile?->willing_to_travel,
            'languages'             => $profile?->languages ?? [],
            'skills'                => $profile?->skills ?? [],
            'summary'               => $profile?->summary,
            'completion_percentage' => $profile?->completion_percentage ?? 0,
            'status'                => $profile?->status,
            'all_document_images'   => $documentImages,
            'documents'             => DocumentResource::collection($allDocuments),
            'video'                 => $videoResource,
        ]);
    }
}

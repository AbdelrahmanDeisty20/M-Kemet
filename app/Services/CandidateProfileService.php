<?php

namespace App\Services;

use App\Http\Resources\CandidateProfileResource;
use App\Models\Document;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Video;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CandidateProfileService
{
    use ApiResponse;

    /**
     * Get logged-in candidate profile details
     */
    public function getProfile(User $user): JsonResponse
    {
        $user->load([
            'candidateProfile.genderRelation',
            'candidateProfile.currentCountry',
            'candidateProfile.experienceLevel',
            'candidateProfile.profession',
            'candidateProfile.targetCountries',
            'documents',
            'video',
        ]);

        $profile = $user->candidateProfile;

        if (!$profile) {
            return $this->notFoundResponse(__('messages.profileNotFound'));
        }

        // Attach documents & video to profile for resource output
        $profile->setRelation('documents', $user->documents);
        $profile->setRelation('video', $user->video);

        return $this->successResponse([
            'profile' => new CandidateProfileResource($profile),
        ], __('messages.profileSuccessFully'));
    }

    /**
     * Update candidate profile data (Professional Info)
     */
    public function updateProfile(User $user, array $data): JsonResponse
    {
        return DB::transaction(function () use ($user, $data) {
            // Update User name if provided
            if (isset($data['name'])) {
                $user->update(['name' => $data['name']]);
            }

            $profile = UserProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['status' => 'pending']
            );

            // Filter out non-profile fields (name, pivot fields) from direct profile update
            $profileData = array_diff_key($data, array_flip(['name', 'target_country_ids']));
            $profile->update($profileData);

            // Sync Target Countries
            if (isset($data['target_country_ids'])) {
                $profile->targetCountries()->sync($data['target_country_ids']);
            }

            return $this->getProfile($user);
        });
    }

    /**
     * Upload or update a user document (personal_photo, national_id, passport, cv)
     */
    public function uploadDocument(User $user, string $documentType, UploadedFile $file): JsonResponse
    {
        $disk = ($documentType === 'personal_photo') ? 'public' : 'private';
        $folder = 'documents/' . $documentType . 's';

        $path = $file->store($folder, $disk);

        // Delete old document of same type if exists
        $existingDoc = Document::where('user_id', $user->id)
            ->where('document_type', $documentType)
            ->first();

        if ($existingDoc) {
            Storage::disk($existingDoc->disk ?? 'private')->delete($existingDoc->file_path);
            $existingDoc->update([
                'file_path'   => $path,
                'disk'        => $disk,
                'is_approved' => false,
                'rejection_reason' => null,
            ]);
            $document = $existingDoc;
        } else {
            $document = Document::create([
                'user_id'       => $user->id,
                'document_type' => $documentType,
                'file_path'     => $path,
                'disk'          => $disk,
                'is_approved'   => false,
            ]);
        }

        return $this->successResponse([
            'document' => [
                'id'            => $document->id,
                'document_type' => $document->document_type,
                'url'           => $document->secure_url,
                'file_path'     => $document->file_path,
            ],
        ], __('messages.documentUploadedSuccessfully'));
    }

    /**
     * Upload candidate intro video
     */
    public function uploadVideo(User $user, UploadedFile $file, ?int $durationSeconds = null): JsonResponse
    {
        $path = $file->store('videos', 'public');
        $fileSizeMb = round($file->getSize() / 1024 / 1024, 2);

        $existingVideo = Video::where('user_id', $user->id)->first();

        if ($existingVideo) {
            Storage::disk('public')->delete($existingVideo->video_path);
            if ($existingVideo->thumbnail_path) {
                Storage::disk('public')->delete($existingVideo->thumbnail_path);
            }

            $existingVideo->update([
                'video_path'       => $path,
                'duration_seconds' => $durationSeconds ?? 0,
                'file_size_mb'     => $fileSizeMb,
                'status'           => 'pending',
                'rejection_reason' => null,
            ]);
            $video = $existingVideo;
        } else {
            $video = Video::create([
                'user_id'          => $user->id,
                'video_path'       => $path,
                'duration_seconds' => $durationSeconds ?? 0,
                'file_size_mb'     => $fileSizeMb,
                'status'           => 'pending',
            ]);
        }

        return $this->successResponse([
            'video' => [
                'id'               => $video->id,
                'video_url'        => $video->video_url,
                'duration_seconds' => $video->duration_seconds,
                'file_size_mb'     => $video->file_size_mb,
                'status'           => $video->status,
            ],
        ], __('messages.videoUploadedSuccessfully'));
    }
}

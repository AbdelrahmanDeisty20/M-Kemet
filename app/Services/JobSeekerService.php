<?php

namespace App\Services;

use App\Http\Resources\JobSeekerCardResource;
use App\Http\Resources\JobSeekerDetailResource;
use App\Models\Bookmark;
use App\Models\User;
use App\Models\UserProfile;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSeekerService
{
    use ApiResponse;

    /**
     * Display a general list of approved job seekers (Card Resource view based on UserProfile).
     */
    public function getJobSeekers(Request $request): JsonResponse
    {
        $query = UserProfile::query()
            ->approved()
            ->with([
                'user.documents',
                'genderRelation',
                'currentCountry',
                'profession',
                'targetCountries',
                'experienceLevel',
                'qualificationRelation',
            ]);

        // Status Filter override if specified, otherwise default is 'approved' via approved() scope
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Search Keyword
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($p) use ($keyword) {
                $p->where('summary', 'like', "%{$keyword}%")
                  ->orWhere('sub_specialization', 'like', "%{$keyword}%")
                  ->orWhereHas('user', function ($u) use ($keyword) {
                      $u->where('name', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('profession', function ($prof) use ($keyword) {
                      $prof->where('title', 'like', "%{$keyword}%")
                           ->orWhere('name', 'like', "%{$keyword}%");
                  });
            });
        }

        // Current Country Filter
        if ($request->filled('current_country_id')) {
            $query->where('current_country_id', $request->input('current_country_id'));
        }

        // Target Country Filter
        if ($request->filled('target_country_id')) {
            $query->whereHas('targetCountries', function ($q) use ($request) {
                $q->where('countries.id', $request->input('target_country_id'));
            });
        }

        // Profession Filter
        if ($request->filled('profession_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('profession_id', $request->input('profession_id'))
                  ->orWhereHas('professions', function ($p) use ($request) {
                      $p->where('professions.id', $request->input('profession_id'));
                  });
            });
        }

        // Experience Years Filter
        if ($request->filled('min_experience')) {
            $query->where('experience_years', '>=', (int) $request->input('min_experience'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $profiles = $query->latest()->paginate($perPage);

        return $this->paginated(
            JobSeekerCardResource::class,
            $profiles,
            __('messages.operationSuccessful')
        );
    }

    /**
     * Display inner detailed candidate profile by User ID or Profile ID (Must be approved).
     */
    public function getJobSeeker(string $id): JsonResponse
    {
        $profile = UserProfile::query()
            ->approved()
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('user_id', $id);
            })
            ->with([
                'user.documents',
                'user.video',
                'genderRelation',
                'currentCountry',
                'qualificationRelation',
                'experienceLevel',
                'profession',
                'targetCountries',
            ])
            ->first();

        if (!$profile) {
            return $this->notFoundResponse(__('messages.profileNotFound'));
        }

        return $this->successResponse([
            'candidate' => new JobSeekerDetailResource($profile),
        ], __('messages.operationSuccessful'));
    }

    /**
     * Toggle bookmark for a job seeker candidate.
     */
    public function toggleBookmark(User $authUser, string $id): JsonResponse
    {
        $candidateUser = User::where('user_type', 'candidate')
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhereHas('candidateProfile', function ($p) use ($id) {
                      $p->where('id', $id);
                  });
            })
            ->first();

        if (!$candidateUser) {
            return $this->notFoundResponse(__('messages.user_not_found'));
        }

        $existingBookmark = Bookmark::where('user_id', $authUser->id)
            ->where('candidate_id', $candidateUser->id)
            ->first();

        if ($existingBookmark) {
            $existingBookmark->delete();
            $isBookmarked = false;
            $message = __('messages.bookmarkRemovedSuccessfully');
        } else {
            Bookmark::create([
                'user_id'      => $authUser->id,
                'candidate_id' => $candidateUser->id,
            ]);
            $isBookmarked = true;
            $message = __('messages.bookmarkAddedSuccessfully');
        }

        return $this->successResponse([
            'candidate_id'  => $candidateUser->id,
            'is_bookmarked' => $isBookmarked,
        ], $message);
    }

    /**
     * Display list of candidates bookmarked by authenticated user.
     */
    public function getBookmarkedJobSeekers(User $authUser, Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);

        $bookmarkedCandidates = $authUser->bookmarkedCandidates()
            ->whereHas('candidateProfile', fn($p) => $p->approved())
            ->with([
                'candidateProfile.genderRelation',
                'candidateProfile.currentCountry',
                'candidateProfile.profession',
                'candidateProfile.targetCountries',
                'candidateProfile.experienceLevel',
                'candidateProfile.qualificationRelation',
                'documents',
            ])
            ->latest('bookmarks.created_at')
            ->paginate($perPage);

        return $this->paginated(
            JobSeekerCardResource::class,
            $bookmarkedCandidates,
            __('messages.operationSuccessful')
        );
    }
}

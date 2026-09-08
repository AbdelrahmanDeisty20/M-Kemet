<?php

namespace App\Services;

use App\Http\Resources\CountryResource;
use App\Http\Resources\GenderResource;
use App\Http\Resources\JobSeekerCardResource;
use App\Http\Resources\JobSeekerDetailResource;
use App\Http\Resources\ProfessionResource;
use App\Models\Bookmark;
use App\Models\Country;
use App\Models\Gender;
use App\Models\Profession;
use App\Models\User;
use App\Models\UserProfile;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSeekerService
{
    use ApiResponse;

    /**
     * Build base query for approved job seekers with search & filter parameters applied.
     */
    protected function buildJobSeekersQuery(Request $request)
    {
        $query = UserProfile::query()
            ->approved()
            ->with([
                'user.documents',
                'genderRelation',
                'currentCountry',
                'profession',
                'professions',
                'targetCountries',
                'experienceLevel',
                'qualificationRelation',
            ]);

        // Status Filter override if specified, default is 'approved' via approved()
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // --- SEARCH CRITERIA (الاسم، المهنة، المهارة) ---
        // 1. Search by Name (الاسم)
        if ($request->filled('name')) {
            $name = $request->input('name');
            $query->whereHas('user', function ($u) use ($name) {
                $u->where('name', 'like', "%{$name}%");
            });
        }

        // 2. Search by Profession (المهنة)
        if ($request->filled('profession')) {
            $professionSearch = $request->input('profession');
            $query->where(function ($q) use ($professionSearch) {
                $q->where('sub_specialization', 'like', "%{$professionSearch}%")
                  ->orWhereHas('profession', function ($p) use ($professionSearch) {
                      $p->where('title_ar', 'like', "%{$professionSearch}%")
                        ->orWhere('title_en', 'like', "%{$professionSearch}%");
                  })
                  ->orWhereHas('professions', function ($p) use ($professionSearch) {
                      $p->where('title_ar', 'like', "%{$professionSearch}%")
                        ->orWhere('title_en', 'like', "%{$professionSearch}%");
                  });
            });
        }

        // 3. Search by Skill (المهارة)
        if ($request->filled('skill')) {
            $skill = $request->input('skill');
            $query->where(function ($q) use ($skill) {
                $q->where('skills', 'like', "%{$skill}%")
                  ->orWhere('summary', 'like', "%{$skill}%")
                  ->orWhere('sub_specialization', 'like', "%{$skill}%");
            });
        }

        // 4. General Keyword search (اسم، مهنة، أو مهارة)
        if ($request->filled('keyword') || $request->filled('q')) {
            $keyword = $request->input('keyword') ?? $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('summary', 'like', "%{$keyword}%")
                  ->orWhere('sub_specialization', 'like', "%{$keyword}%")
                  ->orWhere('skills', 'like', "%{$keyword}%")
                  ->orWhereHas('user', function ($u) use ($keyword) {
                      $u->where('name', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('profession', function ($prof) use ($keyword) {
                      $prof->where('title_ar', 'like', "%{$keyword}%")
                           ->orWhere('title_en', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('professions', function ($prof) use ($keyword) {
                      $prof->where('title_ar', 'like', "%{$keyword}%")
                           ->orWhere('title_en', 'like', "%{$keyword}%");
                  });
            });
        }

        // --- FILTER CRITERIA (الدولة، المهنة، الجنس، جواز السفر) ---

        // 1. Filter by Country ID (الدولة)
        $countryId = $request->input('country_id') ?? $request->input('current_country_id');
        if (!empty($countryId)) {
            $query->where(function ($q) use ($countryId) {
                $q->where('current_country_id', $countryId)
                  ->orWhereHas('user', fn($u) => $u->where('country_id', $countryId))
                  ->orWhereHas('targetCountries', fn($tc) => $tc->where('countries.id', $countryId));
            });
        }

        if ($request->filled('target_country_id')) {
            $targetCountryId = $request->input('target_country_id');
            $query->whereHas('targetCountries', function ($q) use ($targetCountryId) {
                $q->where('countries.id', $targetCountryId);
            });
        }

        // 2. Filter by Profession ID (المهنة)
        if ($request->filled('profession_id')) {
            $professionId = $request->input('profession_id');
            $query->where(function ($q) use ($professionId) {
                $q->where('profession_id', $professionId)
                  ->orWhereHas('professions', function ($p) use ($professionId) {
                      $p->where('professions.id', $professionId);
                  });
            });
        }

        // 3. Filter by Gender ID (الجنس)
        if ($request->filled('gender_id')) {
            $genderId = $request->input('gender_id');
            $query->where('gender_id', $genderId);
        }

        // 4. Filter by Passport Status (حالة جواز السفر)
        if ($request->filled('passport_status') || $request->has('has_passport')) {
            $passportStatus = $request->input('passport_status') ?? $request->input('has_passport');

            if ($passportStatus === 'approved' || $passportStatus === '1' || $passportStatus === 1 || $passportStatus === true || $passportStatus === 'true') {
                $query->whereHas('user.documents', function ($doc) {
                    $doc->where('document_type', 'passport')
                        ->where('is_approved', true);
                });
            } elseif ($passportStatus === 'pending' || $passportStatus === '2' || $passportStatus === 2) {
                $query->whereHas('user.documents', function ($doc) {
                    $doc->where('document_type', 'passport')
                        ->where('is_approved', false);
                });
            } elseif ($passportStatus === 'has_passport' || $passportStatus === 'available' || $passportStatus === '3' || $passportStatus === 3) {
                $query->whereHas('user.documents', function ($doc) {
                    $doc->where('document_type', 'passport');
                });
            } elseif ($passportStatus === 'none' || $passportStatus === 'without_passport' || $passportStatus === '0' || $passportStatus === 0 || $passportStatus === false || $passportStatus === 'false') {
                $query->whereDoesntHave('user.documents', function ($doc) {
                    $doc->where('document_type', 'passport');
                });
            }
        }

        // Additional filters
        if ($request->filled('min_experience')) {
            $query->where('experience_years', '>=', (int) $request->input('min_experience'));
        }

        if ($request->filled('experience_level_id')) {
            $query->where('experience_level_id', $request->input('experience_level_id'));
        }

        return $query;
    }

    /**
     * Display a general list of approved job seekers (Card Resource view based on UserProfile).
     */
    public function getJobSeekers(Request $request): JsonResponse
    {
        $query = $this->buildJobSeekersQuery($request);
        $perPage = (int) $request->input('per_page', 10);
        $profiles = $query->latest()->paginate($perPage);

        return $this->paginated(
            JobSeekerCardResource::class,
            $profiles,
            __('messages.operationSuccessful')
        );
    }

    /**
     * Search job seekers by name, profession, or skill.
     */
    public function searchJobSeekers(Request $request): JsonResponse
    {
        $query = $this->buildJobSeekersQuery($request);
        $perPage = (int) $request->input('per_page', 10);
        $profiles = $query->latest()->paginate($perPage);

        return $this->paginated(
            JobSeekerCardResource::class,
            $profiles,
            __('messages.operationSuccessful')
        );
    }

    /**
     * Filter job seekers by country_id, profession_id, gender_id, and passport_status.
     */
    public function filterJobSeekers(Request $request): JsonResponse
    {
        $query = $this->buildJobSeekersQuery($request);
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

    /**
     * Get aggregated filter options for job seekers (top 6 professions, top 6 countries, genders, passport statuses).
     */
    public function getFilterOptions(): JsonResponse
    {
        $topProfessions = Profession::where('is_active', true)
            ->withCount('candidates')
            ->orderByDesc('candidates_count')
            ->latest()
            ->take(6)
            ->get();

        $topCountries = Country::where('is_active', true)
            ->withCount('candidates')
            ->orderByDesc('candidates_count')
            ->latest()
            ->take(6)
            ->get();

        $genders = Gender::where('is_active', true)->get();

        $passportStatuses = [
            [
                'id'          => 'approved',
                'name_ar'     => 'جواز ساري ومعتمد',
                'name_en'     => 'Valid & Approved Passport',
                'status_code' => 1,
            ],
            [
                'id'          => 'pending',
                'name_ar'     => 'جواز قيد المراجعة',
                'name_en'     => 'Passport Pending Approval',
                'status_code' => 2,
            ],
            [
                'id'          => 'has_passport',
                'name_ar'     => 'يتوفر جواز سفر',
                'name_en'     => 'Has Passport',
                'status_code' => 3,
            ],
            [
                'id'          => 'without_passport',
                'name_ar'     => 'بدون جواز سفر',
                'name_en'     => 'Without Passport',
                'status_code' => 0,
            ],
        ];

        return $this->successResponse([
            'professions'       => ProfessionResource::collection($topProfessions),
            'countries'         => CountryResource::collection($topCountries),
            'genders'           => GenderResource::collection($genders),
            'passport_statuses' => $passportStatuses,
        ], __('messages.operationSuccessful'));
    }
}

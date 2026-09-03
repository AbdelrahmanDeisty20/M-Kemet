<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobSeekerCardResource;
use App\Http\Resources\JobSeekerDetailResource;
use App\Models\Bookmark;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobSeekerController extends Controller
{
    use ApiResponse;

    /**
     * Display a general list of job seekers (Card Resource view).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->where('user_type', 'candidate')
            ->whereHas('candidateProfile')
            ->with([
                'candidateProfile.genderRelation',
                'candidateProfile.currentCountry',
                'candidateProfile.profession',
                'candidateProfile.targetCountries',
                'candidateProfile.experienceLevel',
                'documents',
            ]);

        // Search Keyword
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhereHas('candidateProfile', function ($p) use ($keyword) {
                      $p->where('summary', 'like', "%{$keyword}%")
                        ->orWhere('sub_specialization', 'like', "%{$keyword}%")
                        ->orWhereHas('profession', function ($prof) use ($keyword) {
                            $prof->where('title', 'like', "%{$keyword}%")
                                 ->orWhere('name', 'like', "%{$keyword}%");
                        });
                  });
            });
        }

        // Current Country Filter
        if ($request->filled('current_country_id')) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('current_country_id', $request->input('current_country_id'));
            });
        }

        // Target Country Filter
        if ($request->filled('target_country_id')) {
            $query->whereHas('candidateProfile.targetCountries', function ($q) use ($request) {
                $q->where('countries.id', $request->input('target_country_id'));
            });
        }

        // Profession Filter
        if ($request->filled('profession_id')) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('profession_id', $request->input('profession_id'))
                  ->orWhereHas('professions', function ($p) use ($request) {
                      $p->where('professions.id', $request->input('profession_id'));
                  });
            });
        }

        // Experience Years Filter
        if ($request->filled('min_experience')) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('experience_years', '>=', (int)$request->input('min_experience'));
            });
        }

        // Status Filter (default shows approved candidates or all if specified)
        if ($request->filled('status')) {
            $query->whereHas('candidateProfile', function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $candidates = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'candidates' => JobSeekerCardResource::collection($candidates),
            'pagination' => [
                'total'        => $candidates->total(),
                'count'        => $candidates->count(),
                'per_page'     => $candidates->perPage(),
                'current_page' => $candidates->currentPage(),
                'total_pages'  => $candidates->lastPage(),
            ],
        ], __('messages.operationSuccessful'));
    }

    /**
     * Display inner detailed candidate profile by User ID or Profile ID.
     */
    public function show(string $id): JsonResponse
    {
        $candidate = User::where('user_type', 'candidate')
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhereHas('candidateProfile', function ($p) use ($id) {
                      $p->where('id', $id);
                  });
            })
            ->with([
                'candidateProfile.genderRelation',
                'candidateProfile.currentCountry',
                'candidateProfile.qualification',
                'candidateProfile.experienceLevel',
                'candidateProfile.profession',
                'candidateProfile.targetCountries',
                'documents',
                'video',
            ])
            ->first();

        if (!$candidate || !$candidate->candidateProfile) {
            return $this->notFoundResponse(__('messages.profileNotFound'));
        }

        return $this->successResponse([
            'candidate' => new JobSeekerDetailResource($candidate),
        ], __('messages.operationSuccessful'));
    }

    /**
     * Toggle bookmark for a job seeker candidate.
     */
    public function toggleBookmark(string $id): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user();
        if (!$authUser) {
            return $this->errorResponse(__('messages.unauthenticated'), 401);
        }

        $candidate = User::where('user_type', 'candidate')
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhereHas('candidateProfile', function ($p) use ($id) {
                      $p->where('id', $id);
                  });
            })
            ->first();

        if (!$candidate) {
            return $this->notFoundResponse(__('messages.user_not_found'));
        }

        $existingBookmark = Bookmark::where('user_id', $authUser->id)
            ->where('candidate_id', $candidate->id)
            ->first();

        if ($existingBookmark) {
            $existingBookmark->delete();
            $isBookmarked = false;
            $message = __('messages.bookmarkRemovedSuccessfully') ?? 'تم إزالة الباحث عن العمل من القائمة المحفوظة';
        } else {
            Bookmark::create([
                'user_id'      => $authUser->id,
                'candidate_id' => $candidate->id,
            ]);
            $isBookmarked = true;
            $message = __('messages.bookmarkAddedSuccessfully') ?? 'تم حفظ الباحث عن العمل في القائمة المحفوظة';
        }

        return $this->successResponse([
            'candidate_id'  => $candidate->id,
            'is_bookmarked' => $isBookmarked,
        ], $message);
    }

    /**
     * Display list of candidates bookmarked by authenticated user.
     */
    public function bookmarkedList(Request $request): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user();
        if (!$authUser) {
            return $this->errorResponse(__('messages.unauthenticated'), 401);
        }

        $perPage = (int) $request->input('per_page', 15);

        $bookmarkedCandidates = $authUser->bookmarkedCandidates()
            ->whereHas('candidateProfile')
            ->with([
                'candidateProfile.genderRelation',
                'candidateProfile.currentCountry',
                'candidateProfile.profession',
                'candidateProfile.targetCountries',
                'candidateProfile.experienceLevel',
                'documents',
            ])
            ->latest('bookmarks.created_at')
            ->paginate($perPage);

        return $this->successResponse([
            'candidates' => JobSeekerCardResource::collection($bookmarkedCandidates),
            'pagination' => [
                'total'        => $bookmarkedCandidates->total(),
                'count'        => $bookmarkedCandidates->count(),
                'per_page'     => $bookmarkedCandidates->perPage(),
                'current_page' => $bookmarkedCandidates->currentPage(),
                'total_pages'  => $bookmarkedCandidates->lastPage(),
            ],
        ], __('messages.operationSuccessful'));
    }
}

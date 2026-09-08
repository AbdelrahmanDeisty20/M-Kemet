<?php

namespace App\Services;

use App\Http\Resources\ProfessionResource;
use App\Models\Profession;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProfessionService
{
    use ApiResponse;

    /**
     * Get list of all active professions
     */
    public function getProfessions(): JsonResponse
    {
        $professions = Profession::where('is_active', true)->get();

        return $this->successResponse(
            ProfessionResource::collection($professions),
            __('messages.professionsFetchedSuccessfully')
        );
    }

    /**
     * Get top/popular active professions limited to specified count (default 6)
     */
    public function getTopProfessions(int $limit = 6): JsonResponse
    {
        $professions = Profession::where('is_active', true)
            ->withCount('candidates')
            ->orderByDesc('candidates_count')
            ->latest()
            ->take($limit)
            ->get();

        return $this->successResponse(
            ProfessionResource::collection($professions),
            __('messages.professionsFetchedSuccessfully')
        );
    }
}

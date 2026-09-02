<?php

namespace App\Services;

use App\Http\Resources\ExperienceLevelResource;
use App\Models\ExperienceLevel;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ExperienceLevelService
{
    use ApiResponse;

    /**
     * Get list of all active experience levels
     */
    public function getExperienceLevels(): JsonResponse
    {
        $levels = ExperienceLevel::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->successResponse(
            ExperienceLevelResource::collection($levels),
            __('messages.experienceLevelsFetchedSuccessfully')
        );
    }
}

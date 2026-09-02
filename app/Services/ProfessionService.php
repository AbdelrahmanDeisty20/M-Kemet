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
}

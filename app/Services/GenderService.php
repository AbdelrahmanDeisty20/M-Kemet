<?php

namespace App\Services;

use App\Http\Resources\GenderResource;
use App\Models\Gender;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class GenderService
{
    use ApiResponse;

    /**
     * Get list of all active genders
     */
    public function getGenders(): JsonResponse
    {
        $genders = Gender::where('is_active', true)->get();

        return $this->successResponse(
            GenderResource::collection($genders),
            __('messages.gendersFetchedSuccessfully')
        );
    }
}

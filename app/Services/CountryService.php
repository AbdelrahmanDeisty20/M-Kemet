<?php

namespace App\Services;

use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CountryService
{
    use ApiResponse;

    /**
     * Get list of all active countries
     */
    public function getCountries(): JsonResponse
    {
        $countries = Country::where('is_active', true)->get();

        return $this->successResponse(
            CountryResource::collection($countries),
            __('messages.countriesFetchedSuccessfully')
        );
    }
}

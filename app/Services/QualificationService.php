<?php

namespace App\Services;

use App\Http\Resources\QualificationResource;
use App\Models\Qualification;
use Illuminate\Http\JsonResponse;

class QualificationService
{
    /**
     * Get list of active educational qualifications
     */
    public function getQualifications(): JsonResponse
    {
        $qualifications = Qualification::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => __('messages.qualificationsFetchedSuccessfully'),
            'data'    => [
                'qualifications' => QualificationResource::collection($qualifications),
            ],
        ]);
    }
}

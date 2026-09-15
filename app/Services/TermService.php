<?php

namespace App\Services;

use App\Http\Resources\TermResource;
use App\Models\Term;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TermService
{
    use ApiResponse;

    /**
     * Get list of all active terms & conditions
     */
    public function getTerms(): JsonResponse
    {
        $terms = Term::active()->get();

        return $this->successResponse(
            TermResource::collection($terms),
            __('messages.termsFetchedSuccessfully')
        );
    }

    /**
     * Get a specific term by id or slug
     */
    public function getTerm($idOrSlug): JsonResponse
    {
        $term = Term::active()
            ->where(function ($query) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $query->where('id', $idOrSlug);
                } else {
                    $query->where('slug', $idOrSlug);
                }
            })
            ->first();

        if (!$term) {
            return $this->notFoundResponse(__('messages.notFound'));
        }

        return $this->successResponse(
            new TermResource($term),
            __('messages.termsFetchedSuccessfully')
        );
    }
}

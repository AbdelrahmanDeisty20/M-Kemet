<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\TermService;
use Illuminate\Http\JsonResponse;

class TermController extends Controller
{
    protected TermService $termService;

    public function __construct(TermService $termService)
    {
        $this->termService = $termService;
    }

    /**
     * Display a listing of active terms & conditions.
     */
    public function index(): JsonResponse
    {
        return $this->termService->getTerms();
    }

    /**
     * Display the specified term by ID or slug.
     */
    public function show(string $id): JsonResponse
    {
        return $this->termService->getTerm($id);
    }
}

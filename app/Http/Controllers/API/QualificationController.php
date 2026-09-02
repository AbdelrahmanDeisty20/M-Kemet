<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\QualificationService;
use Illuminate\Http\JsonResponse;

class QualificationController extends Controller
{
    public function __construct(
        protected QualificationService $qualificationService
    ) {}

    /**
     * GET /api/qualifications
     */
    public function index(): JsonResponse
    {
        return $this->qualificationService->getQualifications();
    }
}

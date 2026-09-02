<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ExperienceLevelService;
use Illuminate\Http\JsonResponse;

class ExperienceLevelController extends Controller
{
    protected ExperienceLevelService $experienceLevelService;

    public function __construct(ExperienceLevelService $experienceLevelService)
    {
        $this->experienceLevelService = $experienceLevelService;
    }

    public function index(): JsonResponse
    {
        return $this->experienceLevelService->getExperienceLevels();
    }
}

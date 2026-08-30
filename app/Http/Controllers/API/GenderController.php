<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\GenderService;
use Illuminate\Http\JsonResponse;

class GenderController extends Controller
{
    protected GenderService $genderService;

    public function __construct(GenderService $genderService)
    {
        $this->genderService = $genderService;
    }

    public function index(): JsonResponse
    {
        return $this->genderService->getGenders();
    }
}

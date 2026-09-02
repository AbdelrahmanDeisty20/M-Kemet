<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ProfessionService;
use Illuminate\Http\JsonResponse;

class ProfessionController extends Controller
{
    protected ProfessionService $professionService;

    public function __construct(ProfessionService $professionService)
    {
        $this->professionService = $professionService;
    }

    public function index(): JsonResponse
    {
        return $this->professionService->getProfessions();
    }
}

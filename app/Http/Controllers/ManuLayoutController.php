<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MenuLayoutGenerationService;

class ManuLayoutController extends Controller
{
    //
    protected $menuLayoutGenerationService;

    public function __construct(
        MenuLayoutGenerationService $menuLayoutGenerationService
    ) {
        $this->menuLayoutGenerationService = $menuLayoutGenerationService;
    }

    public function generate(Request $request)
    {
        return $this->menuLayoutGenerationService->generateMenuLayout($request);
    }

}

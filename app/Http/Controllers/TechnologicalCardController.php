<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TechnologicalCardGeneratorService;

class TechnologicalCardController extends Controller
{
    //
    protected $technologicalCardGeneratorService;

    public function __construct(TechnologicalCardGeneratorService $technologicalCardGeneratorService)
    {
        $this->technologicalCardGeneratorService = $technologicalCardGeneratorService;
    }

    public function generate(Request $request)
    {
        $products = $request->input('products', []);
        return $this->technologicalCardGeneratorService->generateTechnologicalCard($products);
    }
}

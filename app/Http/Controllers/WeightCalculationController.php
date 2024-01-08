<?php

namespace App\Http\Controllers;

use App\Services\WeightCalculationService;
use Illuminate\Http\Request;

class WeightCalculationController extends Controller
{
    protected $weightCalculationService;

    public function __construct(WeightCalculationService $weightCalculationService)
    {
        $this->weightCalculationService = $weightCalculationService;
    }

    public function process(Request $request)
    {
        $products = $request->input('products');       

        // Validate that products is not null and is an array
        if (is_null($products) || !is_array($products)) {
            return response()->json(['error' => 'Invalid products data'], 400);
        }

        $processedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        return response()->json(['products' => $processedProducts]);
    }
}

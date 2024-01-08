<?php

namespace App\Http\Controllers;

use App\Services\NutrientCalculationService;
use Illuminate\Http\Request;


class NutrientCalculationController extends Controller
{
    protected $nutrientCalculationService;

    public function __construct(NutrientCalculationService $nutrientCalculationService)
    {
        $this->nutrientCalculationService = $nutrientCalculationService;
    }

    public function calculate(Request $request)
    {
        // Get products from request
        $products = $request->input('products');       
        
        // Validate that products is not null and is an array
        if (is_null($products) || !is_array($products)) {
            return response()->json(['error' => 'Invalid products data'], 400);
        }

        // Calculate macronutrients and micronutrients
        
        $macroNutrients = $this->nutrientCalculationService->calculateMacronutrients($products);
        $microNutrients = $this->nutrientCalculationService->calculateMicronutrients($products);

        $combinedNutrients = array_merge($macroNutrients, $microNutrients);
 
        return response()->json($combinedNutrients);
    }
}

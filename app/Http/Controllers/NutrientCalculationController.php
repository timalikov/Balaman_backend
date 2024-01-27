<?php

namespace App\Http\Controllers;

use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use Illuminate\Http\Request;



class NutrientCalculationController extends Controller
{
    protected $nutrientCalculationService;
    protected $weightCalculationService;

    public function __construct(NutrientCalculationService $nutrientCalculationService, WeightCalculationService $weightCalculationService)
    {
        $this->nutrientCalculationService = $nutrientCalculationService;
        $this->weightCalculationService = $weightCalculationService;
    }

    public function calculate(Request $request)
    {
        // Get products from request
        $products = $request->input('products');       
        
        // Validate that products is not null and is an array
        if (is_null($products) || !is_array($products)) {
            return response()->json(['error' => 'Invalid products data'], 400);
        }

        $processedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        // Calculate modified weights for the products
        $productsWithUpdatedWeights = $this->nutrientCalculationService->calculateWeight($processedProducts);

        // Calculate nutrients for the products
        $productsWithUpdatedNutrients = $this->nutrientCalculationService->calculateNutrients($productsWithUpdatedWeights);

        // Prepare the response
        $response = [
            'products' => $productsWithUpdatedNutrients
        ];
    
        return response()->json($response);
    }
}

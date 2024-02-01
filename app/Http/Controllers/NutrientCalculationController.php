<?php

namespace App\Http\Controllers;

use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;
use Illuminate\Http\Request;



class NutrientCalculationController extends Controller
{
    protected $nutrientCalculationService;
    protected $weightCalculationService;
    protected $productFetchService;

    public function __construct(NutrientCalculationService $nutrientCalculationService, WeightCalculationService $weightCalculationService, ProductFetchService $productFetchService)
    {
        $this->nutrientCalculationService = $nutrientCalculationService;
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;
    }

    public function calculate(Request $request)
    {        
        // Get products from request
        $products = $this->productFetchService->completeProductRequest($request);     

        // $products = $request->input('products');
        // return $products;
       

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

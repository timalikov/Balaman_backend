<?php

namespace App\Http\Controllers;

use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;
use App\Services\TotalWeightService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class NutrientCalculationController extends Controller
{
    protected $nutrientCalculationService;
    protected $weightCalculationService;
    protected $productFetchService;
    protected $totalWeightService;

    public function __construct(NutrientCalculationService $nutrientCalculationService, WeightCalculationService $weightCalculationService, ProductFetchService $productFetchService, TotalWeightService $totalWeightService)
    {
        $this->nutrientCalculationService = $nutrientCalculationService;
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;
        $this->totalWeightService = $totalWeightService;
    }

    public function calculateTotalNutrients(Request $request)
    {        
        // Extract or transform the request data to the expected format
        $requestData = $request->input('products'); 

        // Get products from request
        $products = $this->productFetchService->completeProductRequest($requestData);            

        // Validate that products is not null and is an array
        if (is_null($products) || !is_array($products)) {
            return response()->json(['error' => 'Invalid products data'], 400);
        }

        $processedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        Log::info('Processed Products kilogram: ' . json_encode($processedProducts));

        // Calculate modified weights for the products
        $productsWithUpdatedWeights = $this->nutrientCalculationService->calculateWeight($processedProducts);

        $productsWithUpdatedWeights1 = $this->weightCalculationService->calculateNutrientsForCustomWeight($productsWithUpdatedWeights);

        // Calculate nutrients for the products
        $productsWithUpdatedNutrients = $this->nutrientCalculationService->calculateNutrients($productsWithUpdatedWeights1);

        $totals = $this->totalWeightService->calculateTotals($productsWithUpdatedNutrients);

        // Prepare the response
        $response = [
            // 'products' => $productsWithUpdatedNutrients,
            'totals' => $totals
        ];
    
        return response()->json($response);
    }

    public function calculateProductNutrientDetails(Request $request)
    {        
        // Validate and extract the single product information from the request.
        $productData = $request->validate([
            'product_id' => 'required|numeric',
            'weight' => 'required|numeric',
            'factor_ids' => 'required|array',
        ]);

        // Prepare the data in the expected format for the processing method.
        $requestData = [
            'products' => [$productData] // Wrap the single product data in a 'products' array.
        ];
        
        // Get products from request
        $products = $this->productFetchService->completeProductRequest($requestData['products']);

        // Validate that products is not null and is an array
        if (is_null($products) || !is_array($products)) {
            return response()->json(['error' => 'Invalid products data'], 400);
        }

        $processedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        // Calculate modified weights for the products
        $productsWithUpdatedWeights = $this->nutrientCalculationService->calculateWeight($processedProducts);
      
        $productsWithUpdatedWeights1 = $this->weightCalculationService->calculateNutrientsForCustomWeight($productsWithUpdatedWeights);

        // Calculate nutrients for the products
        $productsWithUpdatedNutrients = $this->nutrientCalculationService->calculateNutrients($productsWithUpdatedWeights1);

        // Prepare the response
        $processedProductDetails = $productsWithUpdatedNutrients[0] ?? null;

        // Return the processed product details directly.
        return response()->json($processedProductDetails);
    }


}

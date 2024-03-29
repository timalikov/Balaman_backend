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

        // Log::info('Calculation Start');

        $customWeightAdjustedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        // Log::info('customWeightAdjustedProducts');
        // Log::info($customWeightAdjustedProducts);
        // Correct

        $weightLossAfterColdProcessing = $this->nutrientCalculationService->calculateWeightForColdProcessing($customWeightAdjustedProducts);

        // Log::info('weightLossAfterColdProcessing');
        // Log::info($weightLossAfterColdProcessing);
        // Correct
        
        $customWeightAdjustedAfterColdProcessing = $this->weightCalculationService->calculateNutrientsForCustomWeight($weightLossAfterColdProcessing);

        // Log::info('customWeightAdjustedAfterColdProcessing');
        // Log::info($customWeightAdjustedAfterColdProcessing);
        // 

        $weightLossAfterThermalProcessing = $this->nutrientCalculationService->calculateWeightForThermalProcessing($customWeightAdjustedAfterColdProcessing);

        // Log::info('weightLossAfterThermalProcessing');
        // Log::info($weightLossAfterThermalProcessing);

        // Calculate nutrients for the products
        $nutrientLossAfterThermalProcessing = $this->nutrientCalculationService->calculateNutrients($weightLossAfterThermalProcessing);

        // Log::info('nutrientLossAfterThermalProcessing');
        // Log::info($nutrientLossAfterThermalProcessing);

        $totals = $this->totalWeightService->calculateTotals($nutrientLossAfterThermalProcessing);

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

        $customWeightAdjustedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        $weightLossAfterColdProcessing = $this->nutrientCalculationService->calculateWeightForColdProcessing($customWeightAdjustedProducts);

        $customWeightAdjustedAfterColdProcessing = $this->weightCalculationService->calculateNutrientsForCustomWeight($weightLossAfterColdProcessing);

        $weightLossAfterThermalProcessing = $this->nutrientCalculationService->calculateWeightForThermalProcessing($customWeightAdjustedAfterColdProcessing);

        // Calculate nutrients for the products
        $nutrientLossAfterThermalProcessing = $this->nutrientCalculationService->calculateNutrients($weightLossAfterThermalProcessing);

        // Prepare the response
        $processedProductDetails = $nutrientLossAfterThermalProcessing[0] ?? null;

        // Return the processed product details directly.
        return response()->json($processedProductDetails);
    }


}

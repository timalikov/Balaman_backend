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
        $requestData = $request->input('products'); 

        $products = $this->productFetchService->completeProductRequest($requestData);            

        if (is_null($products) || !is_array($products)) {
            return response()->json(['error' => 'Invalid products data'], 400);
        }


        $customWeightAdjustedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        $weightLossAfterColdProcessing = $this->nutrientCalculationService->calculateWeightForColdProcessing($customWeightAdjustedProducts);

        $customWeightAdjustedAfterColdProcessing = $this->weightCalculationService->calculateNutrientsForCustomWeightAfterColdProcessing($weightLossAfterColdProcessing);

        $weightLossAfterThermalProcessing = $this->nutrientCalculationService->calculateWeightForThermalProcessing($customWeightAdjustedAfterColdProcessing);

        $nutrientLossAfterThermalProcessing = $this->nutrientCalculationService->calculateNutrients($weightLossAfterThermalProcessing);

        $totals = $this->totalWeightService->calculateTotals($nutrientLossAfterThermalProcessing);

        $response = [
            // 'products' => $nutrientLossAfterThermalProcessing,
            'totals' => $totals
        ];
    
        return response()->json($response);
    }

    public function calculateProductNutrientDetails(Request $request)
    {        
        $productData = $request->validate([
            'product_id' => 'required|numeric',
            'weight' => 'required|numeric',
            'factor_ids' => 'required|array',
        ]);

        $requestData = [
            'products' => [$productData]
        ];
        
        $products = $this->productFetchService->completeProductRequest($requestData['products']);


        if (is_null($products) || !is_array($products)) {
            return response()->json(['error' => 'Invalid products data'], 400);
        }

        $customWeightAdjustedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        $weightLossAfterColdProcessing = $this->nutrientCalculationService->calculateWeightForColdProcessing($customWeightAdjustedProducts);

        $customWeightAdjustedAfterColdProcessing = $this->weightCalculationService->calculateNutrientsForCustomWeightAfterColdProcessing($weightLossAfterColdProcessing);

        $weightLossAfterThermalProcessing = $this->nutrientCalculationService->calculateWeightForThermalProcessing($customWeightAdjustedAfterColdProcessing);

        $nutrientLossAfterThermalProcessing = $this->nutrientCalculationService->calculateNutrients($weightLossAfterThermalProcessing);

        $processedProductDetails = $nutrientLossAfterThermalProcessing[0] ?? null;

        $processedProductDetails['nutrients'] = collect($processedProductDetails['nutrients'])->filter(function ($nutrient) {
            return in_array($nutrient['name'], config('nutrients.nutrient_names'));
        })->values()->all();

        $nutrientNames = config('nutrients.nutrient_names');
        foreach ($nutrientNames as $name) {
            if ($name === 'protein' || $name === 'fat' || $name === 'carbohydrate') {
                continue;
            }
            $found = false;
            foreach ($processedProductDetails['nutrients'] as $nutrient) {
                if ($nutrient['name'] === $name) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $processedProductDetails['nutrients'][] = [
                    'name' => $name,
                    'weight' => 0,
                    'measurement_unit' => 'g',
                ];
            }
        }

        return response()->json($processedProductDetails);
    }


}

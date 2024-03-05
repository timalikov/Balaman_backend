<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;
use App\Services\TechnologicalCardGeneratorService;

class TechnologicalCardController extends Controller
{
    //
    protected $nutrientCalculationService;
    protected $weightCalculationService;
    protected $productFetchService;
    protected $technologicalCardGeneratorService;

    public function __construct(
        NutrientCalculationService $nutrientCalculationService,
        WeightCalculationService $weightCalculationService,
        ProductFetchService $productFetchService,
        TechnologicalCardGeneratorService $technologicalCardGeneratorService,
        )
    {
        $this->nutrientCalculationService = $nutrientCalculationService;
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;
        $this->technologicalCardGeneratorService = $technologicalCardGeneratorService;
    }

    public function generate(Request $request)
    {
        $requestData = $request->input('products');
        $products = $this->productFetchService->completeProductRequest($requestData);     

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

        return $this->technologicalCardGeneratorService->generateTechnologicalCard($nutrientLossAfterThermalProcessing);
    }
}

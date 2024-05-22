<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;
use App\Services\TechnologicalCardGenerationService;

class TechnologicalCardController extends Controller
{
    protected $nutrientCalculationService;
    protected $weightCalculationService;
    protected $productFetchService;
    protected $technologicalCardGenerationService;

    public function __construct(
        NutrientCalculationService $nutrientCalculationService,
        WeightCalculationService $weightCalculationService,
        ProductFetchService $productFetchService,
        TechnologicalCardGenerationService $technologicalCardGenerationService,
        )
    {
        $this->nutrientCalculationService = $nutrientCalculationService;
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;
        $this->technologicalCardGenerationService = $technologicalCardGenerationService;
    }

    public function generate(Request $request)
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

        $name = $request->input('name');
        $description = $request->input('recipe_description');
        return $this->technologicalCardGenerationService->generateTechnologicalCard($name, $description, $nutrientLossAfterThermalProcessing);
    }
}

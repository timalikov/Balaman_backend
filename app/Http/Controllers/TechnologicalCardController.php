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

        $processedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        // Calculate modified weights for the products
        $productsWithUpdatedWeights = $this->nutrientCalculationService->calculateWeight($processedProducts);
        
        $productsWithUpdatedWeights1 = $this->weightCalculationService->calculateNutrientsForCustomWeight($productsWithUpdatedWeights);

        // Calculate nutrients for the products
        $productsWithUpdatedNutrients = $this->nutrientCalculationService->calculateNutrients($productsWithUpdatedWeights1);

        return $this->technologicalCardGeneratorService->generateTechnologicalCard($productsWithUpdatedNutrients);
    }
}

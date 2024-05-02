<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dish;
use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;
use App\Services\TotalWeightService;
use Illuminate\Validation\ValidationException;

class DishNutrientCalculationController extends Controller
{
    protected $nutrientCalculationService;
    protected $weightCalculationService;
    protected $productFetchService;
    protected $totalWeightService;

    public function __construct(
        NutrientCalculationService $nutrientCalculationService,
        WeightCalculationService $weightCalculationService,
        ProductFetchService $productFetchService,
        TotalWeightService $totalWeightService
    ) {
        $this->nutrientCalculationService = $nutrientCalculationService;
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;
        $this->totalWeightService = $totalWeightService;
    }

    public function getTotalNutritrientsOfDishes(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'dishes' => 'sometimes|array',
                'dishes.*' => 'required_with:dishes|exists:dishes,dish_id',
                'products' => 'nullable|array',
                'products.*.product_id' => 'required_with:products|exists:products,product_id',
                'products.*.weight' => 'required_with:products|numeric',
                'products.*.factor_ids' => 'sometimes:products|array',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $dishes = Dish::findMany($request->input('dishes'));
        $totals = $this->calculateDishTotals($dishes);
        $nutrientMap = $this->calculateNutrientMap($dishes); 
        $productNutrientMap = [];

        
        $productsInput = $request->has('products') ? $request->input('products') : [];
        if (!empty($productsInput)) {
            $products = $this->productFetchService->completeProductRequest($productsInput);
            if (!empty($products)) {
                $productData = $this->calculateProductTotals($products);
                $productTotals = $productData['totals'];
                $productNutrientMap = $productData['totals']['nutrient_map']; 
            }
        }


        if (!empty($productTotals) && !empty($productNutrientMap)) {
            foreach ($productTotals as $key => $value) {
                if (isset($totals[$key])) {
                    $totals[$key] += $value;
                }
            }

             // Merge nutrient maps
            foreach ($productNutrientMap as $nutrientInfo) {
                $found = false;
                foreach ($nutrientMap as &$existingNutrient) {
                    if ($existingNutrient['name'] === $nutrientInfo['name']) {
                        $existingNutrient['weight'] += $nutrientInfo['weight'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $nutrientMap[] = $nutrientInfo; // Add new nutrient to the map
                }
            }
        }

        $nutrientNames = config('nutrients.nutrient_names');
        $nutrientMeasurements = config('nutrients.nutrient_mesurement_units');
        if (empty($nutrientMap)) {
            foreach ($nutrientNames as $nutrientName) {
                $nutrientMap[] = [
                    'name' => $nutrientName,
                    'weight' => 0,
                    'measurement_unit' => $nutrientMeasurements[$nutrientName] ?? 'g',
                ];
            }
        } // else check that all nutrients are present that are listed in config('nutrients.nutrient_names')
        else {
            $missingNutrients = array_diff($nutrientNames, array_column($nutrientMap, 'name'));
            foreach ($missingNutrients as $missingNutrient) {
                if ($missingNutrient === 'protein' || $missingNutrient === 'fat' || $missingNutrient === 'carbohydrate') {
                    continue;
                }
                $nutrientMap[] = [
                    'name' => $missingNutrient,
                    'weight' => 0,
                    'measurement_unit' => $nutrientMeasurements[$missingNutrient] ?? 'g',
                ];
            }
        }

        return response()->json([
            'totals' => $totals,
            'nutrientMap' => $nutrientMap,
        ]);
    }

    protected function calculateDishTotals($dishes)
    {
        $totals = [
            'total_price' => 0,
            'total_weight' => 0,
            'total_kilocalories' => 0,

            'total_protein' => 0,
            'total_fat' => 0,
            'total_carbohydrate' => 0,
        ];

        foreach ($dishes as $dish) {
            $totals['total_price'] += $dish->price; // Assuming these properties exist and are named this way
            $totals['total_weight'] += $dish->weight;
            $totals['total_kilocalories'] += $dish->kilocalories;

            $totals['total_protein'] += $dish->protein;
            $totals['total_fat'] += $dish->fat;
            $totals['total_carbohydrate'] += $dish->carbohydrate;
        }

        return $totals;
    }

    protected function calculateProductTotals(array $products)
    {
        $customWeightAdjustedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        // Log::info('customWeightAdjustedProducts');
        // Log::info($customWeightAdjustedProducts);
        // Correct

        $weightLossAfterColdProcessing = $this->nutrientCalculationService->calculateWeightForColdProcessing($customWeightAdjustedProducts);

        // Log::info('weightLossAfterColdProcessing');
        // Log::info($weightLossAfterColdProcessing);
        // Correct
        
        $customWeightAdjustedAfterColdProcessing = $this->weightCalculationService->calculateNutrientsForCustomWeightAfterColdProcessing($weightLossAfterColdProcessing);

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

        return $response;
    }


    protected function calculateNutrientMap($dishes)
    {
        $nutrientMap = [];
        $nutrientNames = config('nutrients.nutrient_names');

        foreach ($dishes as $dish) {
            foreach ($dish->nutrients as $nutrient) {
                $nutrientKey = $nutrient->name; 
                $weight = $nutrient->pivot->weight; 
                if ($nutrientKey === 'protein' || $nutrientKey === 'fat' || $nutrientKey === 'carbohydrate') {
                    continue;
                }
                if (in_array($nutrientKey, $nutrientNames)) {
                    if (!isset($nutrientMap[$nutrientKey])) {
                        
                        $nutrientMap[$nutrientKey] = [
                            'name' => $nutrientKey,
                            'weight' => 0,
                            'measurement_unit' => 'g',
                        ];
                    }
                    $nutrientMap[$nutrientKey]['weight'] += $weight;
                }

            }
        }

        // Convert to a numerically indexed array
        $nutrientMapArray = array_values($nutrientMap);

        return $nutrientMapArray;
    }

}
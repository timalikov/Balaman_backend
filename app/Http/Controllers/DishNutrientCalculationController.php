<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dish;
use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;
use App\Services\TotalWeightService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

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
                'dishes.*' => 'exists:dishes,dish_id',
                'products' => 'nullable|array',
                'products.*.product_id' => 'exists:products,product_id',
                'products.*.weight' => 'numeric',
                'products.*.factor_ids' => 'array',
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
                    $nutrientMap[] = $nutrientInfo; 
                }
            }
        }

        $nutrientNames = config('nutrients.nutrient_names');
        $nutrientMeasurements = config('nutrients.nutrient_mesurement_units');
        if (empty($nutrientMap)) {
            foreach ($nutrientNames as $nutrientName) {
                if ($nutrientName === 'protein' || $nutrientName === 'fat' || $nutrientName === 'carbohydrate') {
                    continue;
                }
                $nutrientMap[] = [
                    'name' => $nutrientName,
                    'weight' => 0,
                    'measurement_unit' => $nutrientMeasurements[$nutrientName] ?? 'g',
                ];
            }
        }
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
            $totals['total_price'] += $dish->price; 
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

        $weightLossAfterColdProcessing = $this->nutrientCalculationService->calculateWeightForColdProcessing($customWeightAdjustedProducts);

        $customWeightAdjustedAfterColdProcessing = $this->weightCalculationService->calculateNutrientsForCustomWeightAfterColdProcessing($weightLossAfterColdProcessing);

        $weightLossAfterThermalProcessing = $this->nutrientCalculationService->calculateWeightForThermalProcessing($customWeightAdjustedAfterColdProcessing);

        $nutrientLossAfterThermalProcessing = $this->nutrientCalculationService->calculateNutrients($weightLossAfterThermalProcessing);

        $totals = $this->totalWeightService->calculateTotals($nutrientLossAfterThermalProcessing);

        $response = [
            'totals' => $totals
        ];

        return $response;
    }


    protected function calculateNutrientMap($dishes)
    {
        $nutrientMap = [];
        $nutrientNames = config('nutrients.nutrient_names');
        $nutrientMeasurements = config('nutrients.nutrient_mesurement_units');

        foreach ($nutrientNames as $name){
            $nutrientMap[$name] = [
                'name' => $name,
                'weight' => 0,
                'measurement_unit' => $nutrientMeasurements[$name] ?? 'g',
            ];
        }
        
        foreach ($dishes as $dish) {
            if ($dish->has_relation_with_products) {
                foreach ($dish->products as $product) {
                    $nutrientsJson = $product['pivot']['nutrients'];
                    $nutrients = json_decode($nutrientsJson);
                    
                    foreach ($nutrients as $nutrient) {
                        $nutrientKey = $nutrient->name;
                        $weight = $nutrient->pivot->weight;

                        if ($nutrientKey === 'protein' || $nutrientKey === 'fat' || $nutrientKey === 'carbohydrate') {
                            continue;
                        }
                        
                        if (isset($nutrientMap[$nutrientKey])) {
                            $nutrientMap[$nutrientKey]['weight'] += $weight;
                        }
                        
                    }
                }
            }
            else{
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
                                'measurement_unit' => $nutrientMeasurements[$nutrientKey] ?? 'g',
                            ];
                        }
                        $nutrientMap[$nutrientKey]['weight'] += $weight;
                    }
    
                }
            }
        }

        // Convert to a numerically indexed array
        $nutrientMapArray = array_values($nutrientMap);

        return $nutrientMapArray;
    }

}
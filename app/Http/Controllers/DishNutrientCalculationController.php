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
        $nutrientMap = $this->calculateNutrientMap($dishes); // Assume this method aggregates dish nutrients

        $productNutrientMap = []; // Assuming this will be structured like the dish nutrient map

        // Process products if provided
        $productsInput = $request->has('products') ? $request->input('products') : [];
        if (!empty($productsInput)) {
            $products = $this->productFetchService->completeProductRequest($productsInput);
            if (!empty($products)) {
                // Assuming calculateProductTotals returns both totals and nutrient_map
                $productData = $this->calculateProductTotals($products);
                $productTotals = $productData['totals'];
                $productNutrientMap = $productData['totals']['nutrient_map']; // Adjust based on actual returned structure
            }
        }


        if (!empty($productTotals) && !empty($productNutrientMap)) {
            // Merge totals
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
            'total_kilocalories_with_fiber' => 0,
            'total_protein' => 0,
            'total_fat' => 0,
            'total_carbohydrate' => 0,
        ];

        foreach ($dishes as $dish) {
            $totals['total_price'] += $dish->price; // Assuming these properties exist and are named this way
            $totals['total_weight'] += $dish->weight;
            $totals['total_kilocalories'] += $dish->kilocalories;
            $totals['total_kilocalories_with_fiber'] += $dish->kilocalories_with_fiber;
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

        return $response;
    }


    protected function calculateNutrientMap($dishes)
    {
        $nutrientMap = [];

        foreach ($dishes as $dish) {
            foreach ($dish->nutrients as $nutrient) {
                $nutrientKey = $nutrient->name; // Unique identifier for the nutrient, e.g., the name
                $weight = $nutrient->pivot->weight; // Assuming there's a 'pivot' table with 'weight'

                // Check if this nutrient is already in our map
                if (!array_key_exists($nutrientKey, $nutrientMap)) {
                    $nutrientMap[$nutrientKey] = [
                        'name' => $nutrientKey, // Adding name for conversion to the desired structure
                        'weight' => 0,
                        'measurement_unit' => $nutrient->measurement_unit,
                    ];
                }

                // Add this dish's nutrient weight to the total
                $nutrientMap[$nutrientKey]['weight'] += $weight;
            }
        }

        // Convert to a numerically indexed array
        $nutrientMapArray = array_values($nutrientMap);

        return $nutrientMapArray;
    }

}
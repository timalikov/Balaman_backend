<?php

namespace App\Services;

use App\Models\Dish;
use Illuminate\Support\Facades\DB; // Added for potential transaction use
use App\Services\ProductFetchService;
use App\Services\WeightCalculationService;
use App\Services\NutrientCalculationService;


class DishCreationService
{
    private $productFetchService;
    private $weightCalculationService;
    private $nutrientCalculationService;

    public function __construct(
        ProductFetchService $productFetchService,
        WeightCalculationService $weightCalculationService,
        NutrientCalculationService $nutrientCalculationService
    ) {
        $this->productFetchService = $productFetchService;
        $this->weightCalculationService = $weightCalculationService;
        $this->nutrientCalculationService = $nutrientCalculationService;
    }
    
    public function createDish(array $validatedData, array $requestDataProducts)
    {
        // Edited: $validatedData is now expected to be an array, not a request object.
        $totalPrice = 0;
        $totalWeight = 0;
        $totalKilocalories = 0;
        $totalKilocaloriesWithFiber = 0;
        $nutrientsTotals = [];

        $totalProtein = 0;
        $totalFat = 0;
        $totalCarbohydrate = 0;

        // Check for 'products' key in the array instead of using request methods.
        if (array_key_exists('products', $validatedData)) {
            $validatedData['has_relation_with_products'] = true;

            // $productsList = $validatedData['products'];

            // \Log::info('Fetching products', ['productsList' => $productsList]);
            $products = $this->productFetchService->completeProductRequest($requestDataProducts);
            \Log::info('Products fetched Dishcreation seederr', ['products' => $products]);


            // Edited: Removed the response()->json part, as service should not return HTTP responses.
            if (is_null($products) || !is_array($products)) {
                throw new \Exception('Invalid products data'); // Throw an exception instead.
            }

            $customWeightAdjustedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

            $weightLossAfterColdProcessing = $this->nutrientCalculationService->calculateWeightForColdProcessing($customWeightAdjustedProducts);

            $customWeightAdjustedAfterColdProcessing = $this->weightCalculationService->calculateNutrientsForCustomWeight($weightLossAfterColdProcessing);

            $weightLossAfterThermalProcessing = $this->nutrientCalculationService->calculateWeightForThermalProcessing($customWeightAdjustedAfterColdProcessing);

            // Calculate nutrients for the products
            $nutrientLossAfterThermalProcessing = $this->nutrientCalculationService->calculateNutrients($weightLossAfterThermalProcessing);
            \Log::info('Nutrient loss after thermal processing Brutto seederrr', ['nutrientLossAfterThermalProcessing' => $nutrientLossAfterThermalProcessing]);

            // Prepare the response
            $processedProductDetails = $nutrientLossAfterThermalProcessing[0] ?? null;

            $productsData = [];
            foreach ($nutrientLossAfterThermalProcessing as $product) {
                $totalPrice += $product['price'];
                $totalWeight += $product['weight'];
                $totalKilocalories += $product['kilocalories'];
                $totalKilocaloriesWithFiber += $product['kilocalories_with_fiber'];
                
                foreach ($product['nutrients'] as $nutrient) {
                    if (!in_array($nutrient['nutrient_id'], [2, 3, 4])) {
                        if (!isset($nutrientsTotals[$nutrient['nutrient_id']])) {
                            $nutrientsTotals[$nutrient['nutrient_id']] = 0;
                        }
                        $nutrientsTotals[$nutrient["nutrient_id"]] += $nutrient["pivot"]['weight'];
                    } else {
                        switch ($nutrient['nutrient_id']) {
                            case 2:
                                $totalProtein += $nutrient["pivot"]['weight'];
                                break;
                            case 3:
                                $totalFat += $nutrient["pivot"]['weight'];
                                break;
                            case 4:
                                $totalCarbohydrate += $nutrient["pivot"]['weight'];
                                break;
                        }
                    }
                }

                $bruttoWeight = $product['brutto_weight'] ?? $product['weight']; // Fallback to 'weight' if 'brutto_weight' is not set
 
                $productsData[] = [
                    'product_id' => $product['product_id'],
                    'weight' => $bruttoWeight,
                    'name' => $product['name'],
                    'kilocalories' => $product['kilocalories'],
                    'price' => $product['price'],
                    'factor_ids' => json_encode($product['factor_ids']),
                    // Edited: Serialize nutrients within service, not in controller
                    'nutrients' => json_encode($product['nutrients'])
                ];
            }

            $validatedData['price'] = $totalPrice;
            $validatedData['weight'] = $totalWeight;
            $validatedData['kilocalories'] = $totalKilocalories;
            $validatedData['kilocalories_with_fiber'] = $totalKilocaloriesWithFiber;

            $validatedData['protein'] = $totalProtein;
            $validatedData['fat'] = $totalFat;
            $validatedData['carbohydrate'] = $totalCarbohydrate;

            $dish = Dish::create($validatedData);
            
            // Edited: Ensure dish is created before attaching products.
            foreach ($productsData as $productId => $details) {
                $dish->products()->attach($productId, $details);
            }
        } else {
            // Create the dish with validated data
            $dish = Dish::create($validatedData);
        }
        
        // Adjusted nutrients handling logic for DishCreationService
        // Assuming $validatedData contains 'nutrients' as an array of nutrients if provided
        $excludedNutrientIds = [2, 3, 4]; // IDs for protein, fat, carbohydrate

        // Initialize an array to hold nutrients data for attachment
        $nutrientsData = [];

        if (isset($validatedData['nutrients']) && is_array($validatedData['nutrients'])) {
            // When nutrients are explicitly provided in $validatedData
            foreach ($validatedData['nutrients'] as $nutrient) {
                if (!in_array($nutrient['nutrient_id'], $excludedNutrientIds)) {
                    $nutrientsData[$nutrient['nutrient_id']] = ['weight' => $nutrient['weight']];
                }
            }
        } else {
            // When nutrients are not explicitly provided, calculate from product data if available
            foreach ($nutrientsTotals as $nutrientId => $total) {
                if (!in_array($nutrientId, $excludedNutrientIds)) {
                    $nutrientsData[$nutrientId] = ['weight' => $total];
                }
            }
        }

        // Attach the prepared nutrients data to the dish
        // Ensure that $dish is a Dish model instance and has a correct relationship method defined for attaching nutrients
        if (!empty($nutrientsData)) {
            foreach ($nutrientsData as $nutrientId => $data) {
                // Attach each nutrient data to the dish
                $dish->nutrients()->attach($nutrientId, $data);
            }
        }



        return $dish;
    }
}













// else {
//     $validatedData['price'] = $request->input('price');
//     $validatedData['weight'] = $request->input('weight', 0);
//     $validatedData['kilocalories'] = $request->input('kilocalories', 0);
//     $validatedData['kilocalories_with_fiber'] = $request->input('kilocalories_with_fiber');

//     // macros
//     $validatedData['protein'] = $request->input('protein', 0);
//     $validatedData['fat'] = $request->input('fat', 0);
//     $validatedData['carbohydrate'] = $request->input('carbohydrate', 0);

//     // Create the dish with validated data
//     $dish = Dish::create($validatedData);
// }

 
// // Attach nutrients' totals to the dish
// $excludedNutrientIds = [2, 3, 4]; // IDs for protein, fat, carbohydrate

// if (!$request->has('nutrients')) {
//     $nutrientsData = [];
//     foreach ($nutrientsTotals as $nutrientId => $total) {
//         // Skip excluded nutrients
//         if (!in_array($nutrientId, $excludedNutrientIds)) {
//             $nutrientsData[$nutrientId] = ['weight' => $total];
//         }
//     }
//     $dish->nutrients()->attach($nutrientsData);
// } elseif ($request->has('nutrients')) {
//     $nutrientsData = [];
//     foreach ($request->input('nutrients') as $nutrient) {
//         // Skip excluded nutrients
//         if (!in_array($nutrient['nutrient_id'], $excludedNutrientIds)) {
//             $nutrientsData[$nutrient['nutrient_id']] = ['weight' => $nutrient['weight']];
//         }
//     }
//     $dish->nutrients()->attach($nutrientsData);
// }



// return $dish;
// }
// }
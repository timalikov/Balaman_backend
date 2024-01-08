<?php
// promt:Great! How can I using this information about Products create data, with showing info about each product, its nutrients with, its factor,


namespace App\Services;
use App\Models\Product;


class NutrientCalculationService
{

    public function calculateMacronutrients($products)
    {
        $totals = [
            'protein' => 0,
            'fat' => 0,
            'carbohydrate' => 0,
            'kilocalories' => 0,
            'price' => 0,
            'weight' => 0
        ];

        foreach ($products as $productData) {

            // coefficient for weight
            $weightCoefficient = $productData['weight'] / 100;

            // apply this coefficient to each nutrient
            $totals['protein'] += $productData['protein'] * $weightCoefficient;
            $totals['fat'] += $productData['fat'] * $weightCoefficient;
            $totals['carbohydrate'] += $productData['carbohydrate'] * $weightCoefficient;
            $totals['kilocalories'] += $productData['kilocalories'] * $weightCoefficient;
            $totals['price'] += $productData['price'];
            $totals['weight'] += $productData['weight'];
        }

        return $totals;

    }
    
    public function calculateMicronutrients($products){

        $totals = [];

        foreach ($products as $productData) {
            // Check if the 'micros' array is present in the product data
            if (isset($productData['micros']) && is_array($productData['micros'])) {
                // Iterate over the nutrients listed in the 'micros' array
                foreach ($productData['micros'] as $nutrient) {
                    if (isset($productData[$nutrient])) {
                        if (!isset($totals[$nutrient])) {
                            $totals[$nutrient] = 0;
                        }
    
                        // Sum the nutrient value
                        $totals[$nutrient] += $productData[$nutrient];
                    }
                }
            }
        }
    
        return $totals;





        // $totals = [
        //     'protein' => 0,
        //     'fat' => 0,
        //     'carbohydrate' => 0,
        //     'fiber' => 0,
        //     'sugar' => 0,
        //     'kilocalories' => 0,
        //     'price' => 0
        // ];
        // foreach ($products as $productData) {
        //     $product = Product::with('factors')->find($productData['id']);

        //     // Initial coefficients for each nutrient
        //     $coefficients = [
        //         'protein' => 1,
        //         'fat' => 1,
        //         'carbohydrate' => 1,
        //         'fiber' => 1,
        //         'sugar' => 1,
        //         'kilocalories' => 1,
        //         'price' => 1
        //     ];

        //     foreach ($product->factors as $factor) {
        //         $coefficients['protein'] *= $factor->pivot->coefficient;
        //         $coefficients['fat'] *= $factor->pivot->coefficient;
        //         $coefficients['carbohydrate'] *= $factor->pivot->coefficient;
        //         $coefficients['fiber'] *= $factor->pivot->coefficient;
        //         $coefficients['sugar'] *= $factor->pivot->coefficient;
        //         $coefficients['kilocalories'] *= $factor->pivot->coefficient;
        //     }

        //       // Use the provided initial nutrient values as the base
        //     $baseValues = [
        //         'protein' => $productData['protein'],
        //         'fat' => $productData['fat'],
        //         'carbohydrate' => $productData['carbohydrate'],
        //         'fiber' => $productData['fiber'] ?? 0,  // Assuming these might be optional
        //         'sugar' => $productData['sugar'] ?? 0,
        //         'kilocalories' => $productData['kilocalories'] ?? 0,
        //         'price' => $productData['price']
        //     ];

        //      // Apply coefficients to initial nutrient values and add to totals
        //     foreach ($totals as $key => &$totalValue) {
        //         $totalValue += $baseValues[$key] * $coefficients[$key];
        //     }

            
        // }


        // return $totals;
    }

    public function processProducts(array $products): array
    {
        foreach ($products as &$product) {
            // Apply your logic here
            // Example: Adding a new field
            $product['processed'] = true;
        }

        return $products;
    }

}
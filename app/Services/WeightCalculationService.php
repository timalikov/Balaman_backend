<?php


namespace App\Services;
use App\Models\Product;


class WeightCalculationService
{
    const BASE_WEIGHT = 100; // Base weight is always 100 grams

    public function calculateNutrientsForCustomWeight(array $products): array
    {
        foreach ($products as &$productData) {
            // Coefficient for weight (assuming base weight is 100g)
            $weightCoefficient = $productData['weight'] / 100;
    
            $productData['kilocalories'] *= $weightCoefficient;
            $productData['kilocalories_with_fiber'] *= $weightCoefficient;

    
            // Apply the coefficient to the weight of each nutrient in the micros array
            foreach ($productData['nutrients'] as &$nutrient) {
                if (isset($nutrient['pivot']['weight']) && is_numeric($nutrient['pivot']['weight'])) {
                    $nutrient['pivot']['weight'] = round($nutrient['pivot']['weight'] * $weightCoefficient, 2);
                }
            }
            

        }
    
        return $products;
    }
}
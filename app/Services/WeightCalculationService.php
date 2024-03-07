<?php


namespace App\Services;
use App\Models\Product;
use App\Models\Factor;
use Illuminate\Support\Facades\Log;

class WeightCalculationService
{
    const BASE_WEIGHT = 100; // Base weight is always 100 grams

    public function calculateNutrientsForCustomWeight(array $products): array
    {
        foreach ($products as &$productData) {
            // Coefficient for weight (assuming base weight is 100g)

            if (isset($productData['brutto_weight'])) {
                $weightCoefficient = $productData['weight'] / $productData['brutto_weight'];
            } else {
                $weightCoefficient = $productData['weight'] / self::BASE_WEIGHT;
            }

            // $weightCoefficient = $productData['weight'] / 100;

            // Log::info('weightCoefficient');
            // Log::info($weightCoefficient);
    
            $productData['kilocalories'] = round($productData['kilocalories'] * $weightCoefficient, 2);
            $productData['kilocalories_with_fiber'] = round($productData['kilocalories_with_fiber'] * $weightCoefficient, 2);

    
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
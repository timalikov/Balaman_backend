<?php


namespace App\Services;
use App\Models\Product;
use App\Models\Factor;
use Illuminate\Support\Facades\Log;

class WeightCalculationService
{
    const BASE_WEIGHT = 100; 

    public function calculateNutrientsForCustomWeight(array $products): array
    {
        foreach ($products as &$productData) {

            if (isset($productData['brutto_weight'])) {
                $weightCoefficient = $productData['weight'] / $productData['brutto_weight'];
            } else {
                $weightCoefficient = $productData['weight'] / self::BASE_WEIGHT;
            }

            if (!isset($productData['price_calculated']) or $productData['price_calculated'] == False){
                $productData['price'] = $productData['price'] * $weightCoefficient;
                $productData['price_calculated'] = True;
            }
            

    
            $productData['kilocalories'] = round($productData['kilocalories'] * $weightCoefficient, 2);
    
            foreach ($productData['nutrients'] as &$nutrient) {
                if (isset($nutrient['pivot']['weight']) && is_numeric($nutrient['pivot']['weight'])) {
                    $nutrient['pivot']['weight'] = round($nutrient['pivot']['weight'] * $weightCoefficient, 2);
                }
            }
            

        }
    
        return $products;
    }

    public function calculateNutrientsForCustomWeightAfterColdProcessing(array $products): array
    {
        foreach ($products as &$productData) {

            if (isset($productData['brutto_weight'])) {
                $weightCoefficient = $productData['weight'] / $productData['brutto_weight'];
            } else {
                continue;
            }


            $productData['kilocalories'] = round($productData['kilocalories'] * $weightCoefficient, 2);
            $productData['price'] = round($productData['price'] * $weightCoefficient, 2);
    
            foreach ($productData['nutrients'] as &$nutrient) {
                if (isset($nutrient['pivot']['weight']) && is_numeric($nutrient['pivot']['weight'])) {
                    $nutrient['pivot']['weight'] = round($nutrient['pivot']['weight'] * $weightCoefficient, 2);
                }
            }
            

        }
    
        return $products;
    }
}
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
    
            // Apply this coefficient to each macronutrient
            $productData['protein'] *= $weightCoefficient;
            $productData['fat'] *= $weightCoefficient;
            $productData['carbohydrate'] *= $weightCoefficient;
            $productData['kilocaries'] *= $weightCoefficient;
            $productData['fiber'] *= $weightCoefficient;
            $productData['total_sugar'] *= $weightCoefficient;
            $productData['saturated_fat'] *= $weightCoefficient;
            $productData['kilocaries_with_fiber'] *= $weightCoefficient;

    
            // Apply the coefficient to each micronutrient in micros array
            foreach ($productData['micros'] as &$micro) {
                if (isset($micro['amount']) && is_numeric($micro['amount'])) {
                    $micro['amount'] *= $weightCoefficient;
                }
            }

        }
    
        return $products;
    }
}
<?php


namespace App\Services;
use App\Models\Product;
use App\Models\Factor;
use App\Models\Nutrient;

class TotalWeightService 
{

    public function calculateTotals(array $products)
    {
        $nutrientMap = [];

        $total_price = 0;
        $total_weight = 0;
        $total_kilocalories = 0;
        $total_kilocalories_with_fiber = 0;

        foreach($products as &$productData){
            if (isset($productData['weight'], $productData['price'], $productData['kilocalories'], $productData['kilocalories_with_fiber'])) {
                $total_price += $productData['price'];
                $total_weight += $productData['weight'];
                $total_kilocalories += $productData['kilocalories'];
                $total_kilocalories_with_fiber += $productData['kilocalories_with_fiber'];
            }
            if (isset($productData['nutrients'])){
                foreach ($productData['nutrients'] as $nutrient) {
                    if (isset($nutrient['name']) && isset($nutrient['pivot']['weight'])) {
                        $nutrientMap[$nutrient['name']] = (isset($nutrientMap[$nutrient['name']]) ? $nutrientMap[$nutrient['name']] : 0) + $nutrient['pivot']['weight'];
                    }
                }
            }
        }
        unset($productData); // Unset reference to the last element

        return [
            'total_price' => $total_price,
            'total_weight' => $total_weight,
            'total_kilocalories' => $total_kilocalories,
            'total_kilocalories_with_fiber' => $total_kilocalories_with_fiber,
            'nutrient_map' => $nutrientMap
        ];
    }

}
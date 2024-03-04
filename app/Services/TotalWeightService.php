<?php

namespace App\Services;

use App\Models\Nutrient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TotalWeightService 
{
    public function calculateTotals(array $products): array
    {
        $nutrientMap = new Collection();

        $totals = [
            'total_price' => 0,
            'total_weight' => 0,
            'total_kilocalories' => 0,
            'total_kilocalories_with_fiber' => 0,
        ];

        foreach ($products as $productData) {
            $this->processProductData($productData, $totals, $nutrientMap);
        }

        // Round weights in nutrientMap after all processing
        $nutrientMap->transform(function ($nutrient) {
            $nutrient->weight = round($nutrient->weight, 2);
            return $nutrient;
        });

        $totals['nutrient_map'] = $nutrientMap->values(); // Convert to array values if needed

        return $totals;
    }

    protected function processProductData(array $productData, array &$totals, Collection $nutrientMap): void
    {
        if (isset($productData['weight'], $productData['price'], $productData['kilocalories'], $productData['kilocalories_with_fiber'])) {
            $totals['total_price'] += $productData['price'];
            $totals['total_weight'] += $productData['weight'];
            $totals['total_kilocalories'] += $productData['kilocalories'];
            $totals['total_kilocalories_with_fiber'] += $productData['kilocalories_with_fiber'];
        }

        if (isset($productData['nutrients'])) {
            foreach ($productData['nutrients'] as $nutrientData) {
                $this->aggregateNutrient($nutrientData, $nutrientMap);
            }
        }
    }

    protected function aggregateNutrient(array $nutrientData, Collection $nutrientMap): void
    {
        Log::info($nutrientData['measurement_unit'] . 'klaramen');
        if (isset($nutrientData['name'], $nutrientData['pivot']['weight'], $nutrientData['measurement_unit'])) {
            $name = $nutrientData['name'];
            $weight = $nutrientData['pivot']['weight'];
            $unit = $nutrientData['measurement_unit'];

            if (!$nutrientMap->has($name)) {
                $nutrientMap->put($name, new Nutrient([
                    'name' => $name,
                    'weight' => 0,
                    'measurement_unit' => $unit,
                ]));
            }
            
            $nutrient = $nutrientMap->get($name);
            $nutrient->weight += $weight;
        }
    }
}

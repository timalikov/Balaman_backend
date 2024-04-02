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
            // Initialize macronutrients with default values
            'total_protein' => 0,
            'total_fat' => 0,
            'total_carbohydrate' => 0,
        ];

        foreach ($products as $productData) {
            $this->processProductData($productData, $totals, $nutrientMap);
        }

        // Round weights in nutrientMap after all processing
        $nutrientMap->transform(function ($nutrient) {
            $nutrient->weight = round($nutrient->weight, 2);
            return $nutrient;
        });

        // Extract macronutrients' values and add them to totals
        $this->addMacronutrientsToTotals($nutrientMap, $totals);

        $totals['nutrient_map'] = $nutrientMap->values(); // Convert to array values if needed

        return $totals;
    }

    protected function addMacronutrientsToTotals(Collection $nutrientMap, array &$totals): void
    {
        // Map the specific database names to the totals keys
        $macronutrientsMap = [
            'protein' => 'total_protein',
            'fat' => 'total_fat',
            'carbohydrate' => 'total_carbohydrate',
        ];

        foreach ($macronutrientsMap as $dbName => $totalKey) {
            if ($nutrientMap->has($dbName)) {
                $nutrient = $nutrientMap->get($dbName);
                // Assuming the weight is the total amount of each macronutrient
                $totals[$totalKey] = $nutrient->weight;
                // Remove the macronutrient from nutrientMap after adding its total
                $nutrientMap->forget($dbName);
            }
        }
    }


    protected function processProductData(array $productData, array &$totals, Collection $nutrientMap): void
    {
        if (isset($productData['weight'], $productData['price'], $productData['kilocalories'], $productData['kilocalories_with_fiber'])) {
            $totals['total_price'] += $productData['price'];
            $totals['total_weight'] += $productData['weight'];
            $totals['total_kilocalories'] += round($productData['kilocalories'], 2);
            $totals['total_kilocalories_with_fiber'] += round($productData['kilocalories_with_fiber'], 2);

        }

        if (isset($productData['nutrients'])) {
            foreach ($productData['nutrients'] as $nutrientData) {
                $this->aggregateNutrient($nutrientData, $nutrientMap);
            }
        }
    }

    protected function aggregateNutrient(array $nutrientData, Collection $nutrientMap): void
    {
        // Log::info($nutrientData['measurement_unit'] . 'klaramen');
        if (isset($nutrientData['name'], $nutrientData['pivot']['weight'], $nutrientData['measurement_unit'])) {
            $name = $nutrientData['name'];
            $weight = $nutrientData['pivot']['weight'];
            $unit = $nutrientData['measurement_unit'];

            $nutrientNames = config('nutrients.nutrient_names');
            // Only aggregate if the nutrient's name is in the $nutrientNames array
            if (in_array($name, $nutrientNames, true)) {
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
}

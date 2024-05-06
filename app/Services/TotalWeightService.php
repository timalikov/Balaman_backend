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

        // check if nutrientMap has all the nutrient names from nutrientNames except macronutrients
        $nutrientNames = config('nutrients.nutrient_names');
        $nutrientMeasurements = config('nutrients.nutrient_mesurement_units');
        foreach ($nutrientNames as $name) {
            if (!$nutrientMap->has($name)) {
                if ($name === 'protein' || $name === 'fat' || $name === 'carbohydrate') {
                    continue;
                }
                $nutrientMap->put($name, new Nutrient([
                    'name' => $name,
                    'weight' => 0,
                    'measurement_unit' => $nutrientMeasurements[$name],
                ]));
            }
        }
        $totals['nutrient_map'] = $nutrientMap->values(); 

        return $totals;
    }

    protected function addMacronutrientsToTotals(Collection $nutrientMap, array &$totals): void
    {
        $macronutrientsMap = [
            'protein' => 'total_protein',
            'fat' => 'total_fat',
            'carbohydrate' => 'total_carbohydrate',
        ];

        foreach ($macronutrientsMap as $dbName => $totalKey) {
            if ($nutrientMap->has($dbName)) {
                $nutrient = $nutrientMap->get($dbName);

                $totals[$totalKey] = $nutrient->weight;

                $nutrientMap->forget($dbName);
            }
        }
    }


    protected function processProductData(array $productData, array &$totals, Collection $nutrientMap): void
    {
        if (isset($productData['weight'], $productData['price'], $productData['kilocalories'])) {
            $totals['total_price'] += $productData['price'];
            $totals['total_weight'] += $productData['weight'];
            $totals['total_kilocalories'] += round($productData['kilocalories'], 2);
        }

        $nutrientNames = config('nutrients.nutrient_names');
        $nutrientMeasurements = config('nutrients.nutrient_mesurement_units');
        if (isset($productData['nutrients'])) {
            foreach ($productData['nutrients'] as $nutrientData) {
                $this->aggregateNutrient($nutrientData, $nutrientMap);
            }
        } else {
            foreach ($nutrientNames as $name) {
                if ($name === 'protein' || $name === 'fat' || $name === 'carbohydrate') {
                    continue;
                }
                $nutrientMap->put($name, new Nutrient([
                    'name' => $name,
                    'weight' => 0,
                    'measurement_unit' => $nutrientMeasurements[$name],
                ]));
            }
        }

        if ($productData['nutrients'] == []) {
            foreach ($nutrientNames as $name) {
                if ($name === 'protein' || $name === 'fat' || $name === 'carbohydrate') {
                    continue;
                }
                $nutrientMap->put($name, new Nutrient([
                    'name' => $name,
                    'weight' => 0,
                    'measurement_unit' => $nutrientMeasurements[$name],
                ]));
            }
        }
    }

    protected function aggregateNutrient(array $nutrientData, Collection $nutrientMap): void
    {
        $nutrientNames = config('nutrients.nutrient_names');
        $nutrientMeasurements = config('nutrients.nutrient_mesurement_units');

        if (isset($nutrientData['name'], $nutrientData['pivot']['weight'], $nutrientData['measurement_unit'])) {
            $name = $nutrientData['name'];
            $weight = $nutrientData['pivot']['weight'];
            $unit = $nutrientData['measurement_unit'];

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

        // check if thr nutrientmap has all the nutrient names from nutrientNames
        foreach ($nutrientNames as $name) {
            if (!$nutrientMap->has($name)) {
                if ($name === 'protein' || $name === 'fat' || $name === 'carbohydrate') {
                    continue;
                }
                $nutrientMap->put($name, new Nutrient([
                    'name' => $name,
                    'weight' => 0,
                    'measurement_unit' => $nutrientMeasurements[$name],
                ]));
            }
        }
    }
}

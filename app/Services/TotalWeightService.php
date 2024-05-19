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

        $nutrientNames = config('nutrients.nutrient_names');
        $nutrientMeasurements = config('nutrients.nutrient_mesurement_units');
        foreach ($nutrientNames as $name) {
                $nutrientMap->put($name, new Nutrient([
                    'name' => $name,
                    'weight' => 0,
                    'measurement_unit' => $nutrientMeasurements[$name],
                ]));
        }

        $totals = [
            'total_price' => 0,
            'total_weight' => 0,
            'total_kilocalories' => 0,

            'total_protein' => 0,
            'total_fat' => 0,
            'total_carbohydrate' => 0,
        ];

        foreach ($products as $productData) {
            $this->processProductData($productData, $totals, $nutrientMap);
        }
        
        $totals['nutrient_map'] = $nutrientMap->values(); 

        return $totals;
    }

    protected function processProductData(array $productData, array &$totals, Collection $nutrientMap): void
    {
        if (isset($productData['weight'], $productData['price'], $productData['kilocalories'])) {
            $totals['total_price'] += $productData['price'];
            $totals['total_weight'] += $productData['weight'];
            $totals['total_kilocalories'] += round($productData['kilocalories'], 2);
        }

        if (isset($productData['nutrients'])) {
            foreach ($productData['nutrients'] as $nutrientData) {
                $this->aggregateNutrient($nutrientData, $nutrientMap, $totals);
            }
        } 

    }

    protected function aggregateNutrient(array $nutrientData, Collection $nutrientMap, array &$totals): void
    {
        
        if (isset($nutrientData['name'], $nutrientData['pivot']['weight'], $nutrientData['measurement_unit'])) {
            $name = $nutrientData['name'];
            $weight = $nutrientData['pivot']['weight'];
            $unit = $nutrientData['measurement_unit'];

            if($nutrientMap->has($name)) {
                $nutrient = $nutrientMap->get($name);
                $nutrient->weight += $weight;
            }

            if($name === 'protein' || $name === 'fat' || $name === 'carbohydrate') {
                $totals["total_{$name}"] += $weight;
            }
        
        }
    }
}

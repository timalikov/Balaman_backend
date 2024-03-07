<?php


namespace App\Services;
use App\Models\Product;
use App\Models\Factor;
use App\Models\Nutrient;



class NutrientCalculationService
{

    public function calculateWeight(array $products)
    {
        foreach ($products as &$productData) { // Use reference (&) to modify original array elements
            if (isset($productData['factor_ids'], $productData['weight'], $productData['product_id'])) {
                $productId = $productData['product_id'];
                $factorIds = $productData['factor_ids'];
                $weight = $productData['weight'];

                $coefficient = $this->getCoefficients($productId, $factorIds);

                // Update the weight in the $products array
                $productData['weight'] = round($productData['weight'] * $coefficient, 2);
                if (!isset($productData['brutto_weight'])) {
                    $productData['brutto_weight'] = $weight;
                } 
            }
        }
        unset($productData); // Unset reference to the last element

        return $products;
    }

    public function calculateWeightForThermalProcessing(array $products)
    {
        foreach ($products as &$productData) {
            if (isset($productData['factor_ids'], $productData['weight'], $productData['product_id'])) {
                // write condition 
                $productId = $productData['product_id'];
                $factorIds = $productData['factor_ids'];
                $weight = $productData['weight'];

                // Filter out factor_id = 1 from the factorIds array
                $filteredFactorIds = array_filter($factorIds, function($factorId) {
                    return $factorId != 1;
                });

                // If there are no remaining factors, skip the coefficient calculation
                if (empty($filteredFactorIds)) {
                    continue; // Skip this iteration, move to the next product
                }

                $coefficient = $this->getCoefficients($productId, $filteredFactorIds);

                // Update the weight in the $products array
                $productData['weight'] = round($productData['weight'] * $coefficient, 2);
                if (!isset($productData['brutto_weight'])) {
                    $productData['brutto_weight'] = $weight;
                } 
            }
        }
        unset($productData); // Unset reference to the last element

        return $products;
    }


    public function calculateWeightForColdProcessing(array $products)
    {
        foreach ($products as &$productData) {
            if (isset($productData['factor_ids'], $productData['weight'], $productData['product_id'])) {
                // Check if factor_ids contains the value 1
                if (in_array(1, $productData['factor_ids'])) {
                    $productId = $productData['product_id'];
                    $factorIds = [1]; // Consider only factor_id = 1 for calculation
                    $weight = $productData['weight'];

                    $coefficient = $this->getCoefficients($productId, $factorIds);

                    // Update the weight in the $products array only for factor_id = 1
                    $productData['weight'] = round($productData['weight'] * $coefficient, 2);
                    if (!isset($productData['brutto_weight'])) {
                        $productData['brutto_weight'] = $weight;
                    } 
                } else {
                    // If factor_ids does not contain the value 1, skip the coefficient calculation
                    continue; // Skip this iteration, move to the next product
                }
            }
        }
        unset($productData); // Unset reference to the last element

        return $products;
    }



    public function getCoefficients($productId, $factorIds)
    {
        $product = Product::findOrFail($productId);

        $coefficientProduct = 1;

        foreach($factorIds as $factorId){
            $coefficient = \DB::table('weight_losses') // Use the table name directly
                            ->where('product_id', $productId)
                            ->where('factor_id', $factorId)
                            ->value('coefficient'); // Assuming 'coefficient' is the column name

            if ($coefficient !== null and $coefficient !== 0) {
                $coefficientProduct *= $coefficient;
            }
        }
    
        return $coefficientProduct;
    }
    
    public function calculateNutrients(array $products)
    {
        foreach ($products as &$productData) {
            if (isset($productData['factor_ids'], $productData['nutrients'])) {
                $factorIds = $productData['factor_ids'];
                $productId = $productData['product_id'];

                // Iterate over each nutrient and apply the coefficient if available
                foreach ($productData['nutrients'] as &$nutrient) {
                    $nutrientCoefficient = $this->getNutrientCoefficients($productId, $factorIds, $nutrient['nutrient_id']);

                    // Check if a coefficient exists for the nutrient
                    if ($nutrientCoefficient !== null) {
                        $nutrient['pivot']['weight'] = round($nutrient['pivot']['weight'] * $nutrientCoefficient, 2);
                    }
                    // If no coefficient, leave the nutrient value as is
                }
            }
        }
        unset($productData, $nutrient); // Unset reference to the last elements

        return $products;
    }

    /**
     * Retrieve the coefficient for a given nutrient and set of factor IDs
     */
    private function getNutrientCoefficients($productCategoryId, $factorIds, $nutrientId)
    {
        $coefficientProduct = 1;
        foreach($factorIds as $factorId){
            $coefficient = \DB::table('nutrient_losses_by_products') // Use the table name directly
                            ->where('product_id', $productCategoryId)
                            ->where('factor_id', $factorId)
                            ->where('nutrient_id', $nutrientId)
                            ->value('coefficient'); // Assuming 'coefficient' is the column name

            if ($coefficient !== null and $coefficient !== 0) {
                $coefficientProduct *= $coefficient;
            }
        }

        return $coefficientProduct;
    }



}
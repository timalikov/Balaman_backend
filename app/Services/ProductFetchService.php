<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Factor;
use App\Models\Nutrient;
use Illuminate\Http\Request;


class ProductFetchService {
    
    public function completeProductRequest(Request $request)
    {
        $products = $request->input('products');
        foreach ($products as &$productData) {
            if (isset($productData['product_id'])) {
                $productId = $productData['product_id'];
                $productDetails = $this->fetchProductDetails($productId);

                if ($productDetails) {
                    // Merge the details from the database with the existing data
                    $productData = array_merge($productData, $productDetails);
                }
            }
        }
        unset($productData); // Unset reference to last element


        return $products;

    }

    private function fetchProductDetails($productId)
    {
        // Fetch the product by its ID along with related data
        // Fetch the product by its ID along with related data
        $product = Product::with([
            // Include the micronutrients ('nutrients') associated with the product
            'nutrients' => function ($query) {
                // Ensure to fetch the weight from the pivot table for each nutrient
                $query->withPivot('weight');
            }, 
    
            // Include the product's category
            'productCategory' => function ($query) {
                // Select only the necessary fields from the productCategory table
                // Adjust the field names if they are different in your database
                $query->select('product_category_id', 'name');
            }
        ])
        // Filter the product by its unique ID
        ->where('product_id', $productId)
        // Fetch the first product that matches the criteria or fail
        ->firstOrFail();

        if (!$product) {
            return null;
        }

        // Convert the product object to an array and return
        return $product->toArray();
    }

}
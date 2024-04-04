<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\Dish;
use App\Models\DishProduct;
use App\Services\DishCreationService;
use Illuminate\Support\Facades\Http;

class DishesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            // If your route is protected with auth:api middleware, you might need an API token or bearer token here.
            // 'Authorization' => 'Bearer ' . $token,
        ])->post(config('app.url') . '/api/dishes', [
            'bls_code' => rand(1000, 9999),
            'name' => 'Mediterranean Salad',
            'description' => 'A fresh salad with olives, feta, and tomatoes',
            'recipe_description' => 'Chop all ingredients and mix in a bowl',
            'dish_category_id' => 1, // Make sure this ID exists in your dish_categories table
            'image_url' => 'http://example.com/images/salad.jpg',
            'health_factor' => 5,
            'products' => [
                // Include product details as needed
                [
                    'product_id' => 81,
                    'weight' => 100,
                    'factor_ids' => [1, 2, 3],
                ],
                // More products as necessary
            ],
            // Include any additional necessary fields
        ]);
        
        // Check the response
        if ($response->successful()) {
            $data = $response->json();
            // Success logic here
        } elseif ($response->failed()) {
            $error = $response->json();
            // Error handling here
        }
    }
}



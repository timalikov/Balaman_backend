<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProductCategoriesTableSeeder::class,
            ProductsTableSeeder::class,
            
            NutrientsTableSeeder::class,

            FactorsTableSeeder::class,
            WeightLossesTableSeeder::class,

            NutrientLossesByProductsTableSeeder::class,
            NutrientsProductsTableSeeder::class,

            PermissionsTableSeeder::class,
            RolesTableSeeder::class,

            DishCategoriesTableSeeder::class,

            MealTimesTableSeeder::class,

            // Run the following command to seed dishes:
            // php artisan app:seed-dishes

        ]);
    
               
    }
}

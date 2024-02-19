<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

            NutrientLossesByCategoriesTableSeeder::class,
            NutrientsProductsTableSeeder::class,

            PermissionsTableSeeder::class,
            RolesTableSeeder::class,

            DishCategoriesTableSeeder::class,

            MealTimesTableSeeder::class,


        ]);
    
               
    }
}

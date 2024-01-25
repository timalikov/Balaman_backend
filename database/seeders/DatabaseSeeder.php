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
            MicrosSeeder::class,
            MicrosProductsTableSeeder::class,
            FactorsTableSeeder::class,
            WeightLossesTableSeeder::class,
            MicrosLossesByCategoriesTableSeeder::class,
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
        ]);
    
               
    }
}

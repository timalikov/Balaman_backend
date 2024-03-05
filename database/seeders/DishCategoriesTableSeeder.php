<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DishCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //write seeder
        $dishCategories = [
            [
                'name' => 'Супы',
            ],
            [
                'name' => 'Горячие блюда',
            ],
            [
                'name' => 'Гарниры',
                
            ],
            [
                'name' => 'Салаты',
        
            ],
            [
                'name' => 'Десерты',
                
            ],
            [
                'name' => 'Напитки',
                
            ],
        ];

        foreach ($dishCategories as $dishCategory) {
            DB::table('dish_categories')->insert($dishCategory);

        }
    }
}

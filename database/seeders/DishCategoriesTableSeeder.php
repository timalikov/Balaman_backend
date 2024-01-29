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
                'dish_category_id' => 1,
                'name' => 'Супы',
            ],
            [
                'dish_category_id' => 2,
                'name' => 'Горячие блюда',
            ],
            [
                'dish_category_id' => 3,
                'name' => 'Гарниры',
                
            ],
            [
                'dish_category_id' => 4,
                'name' => 'Салаты',
        
            ],
            [
                'dish_category_id' => 5,
                'name' => 'Десерты',
                
            ],
            [
                'dish_category_id' => 6,
                'name' => 'Напитки',
                
            ],
        ];

        foreach ($dishCategories as $dishCategory) {
            DB::table('dish_categories')->insert($dishCategory);

        }
    }
}

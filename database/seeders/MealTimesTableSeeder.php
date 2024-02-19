<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MealTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         // Disable foreign key checks to avoid constraint violations
         Schema::disableForeignKeyConstraints();
         // Truncate the table
         DB::table('meal_times')->truncate();
         // Enable foreign key checks
         Schema::enableForeignKeyConstraints();
 
 
         $meal_times = [
             ['name' => 'Breakfast'],
             ['name' => 'Snack 1'],
             ['name' => 'Lunch'],
             ['name' => 'Snack 2'],
             ['name' => 'Dinner']
         ];
 
         foreach ($meal_times as $meal) {
             DB::table('meal_times')->insert($meal);
         }
    }
}

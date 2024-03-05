<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeightLossSeeder extends Seeder
{
    public function run()
    {
        // Add data for factor_id = 5
        DB::table('weight_losses')->insert([
            'product_id' => 226,
            'factor_id' => 5,
            'coefficient' => 1, // Example coefficient, adjust as needed
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add data for factor_id = 8
        DB::table('weight_losses')->insert([
            'product_id' => 226,
            'factor_id' => 8,
            'coefficient' => 1, // Example coefficient, adjust as needed
            'created_at' => now(),
            'updated_at' => now(),
        ]);

         // Add data for factor_id = 5
         DB::table('weight_losses')->insert([
            'product_id' => 230,
            'factor_id' => 5,
            'coefficient' => 1, // Example coefficient, adjust as needed
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add data for factor_id = 8
        DB::table('weight_losses')->insert([
            'product_id' => 230,
            'factor_id' => 8,
            'coefficient' => 1, // Example coefficient, adjust as needed
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

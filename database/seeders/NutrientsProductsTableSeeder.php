<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NutrientsProductsTableSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks to avoid constraint violations
        Schema::disableForeignKeyConstraints();
        // Truncate the table
        DB::table('nutrients_products')->truncate();
        // Enable foreign key checks
        Schema::enableForeignKeyConstraints();

        for ($productId = 1; $productId <= 696; $productId++) {
            $filePath = storage_path("app/database_data/nutrients/csv_data/$productId.csv");

            if (!file_exists($filePath)) {
                continue;  // Skip if file does not exist
            }

            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            foreach ($csv->getRecords() as $record) {
                
                if (is_null($record['weight']) || $record['weight'] === '') {
                    continue;  // Skip the row if weight is null
                }

                DB::table('nutrients_products')->insert([
                    'nutrient_id' => (int) $record['nutrient_id'],
                    'product_id'  => (int) $record['product_id'],
                    'weight'      => (float) $record['weight'],
                ]);
            }
        }
    }
}

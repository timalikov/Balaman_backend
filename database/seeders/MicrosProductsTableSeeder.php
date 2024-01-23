<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MicrosProductsTableSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks to avoid constraint violations
        Schema::disableForeignKeyConstraints();
        // Truncate the table
        DB::table('micros_products')->truncate();
        // Enable foreign key checks
        Schema::enableForeignKeyConstraints();

        for ($productId = 1; $productId <= 696; $productId++) {
            $filePath = storage_path("app/database_data/micros/csv_data/$productId.csv");

            if (!file_exists($filePath)) {
                continue;  // Skip if file does not exist
            }

            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            foreach ($csv->getRecords() as $record) {
                
                if (is_null($record['weight']) || $record['weight'] === '') {
                    continue;  // Skip the row if weight is null
                }

                DB::table('micros_products')->insert([
                    'micro_id' => $record['micro_id'],
                    'product_id' => $record['product_id'], 
                    'weight' => $record['weight'],
                ]);
            }
        }
    }
}

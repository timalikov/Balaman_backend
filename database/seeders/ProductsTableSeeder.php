<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Illuminate\Support\Facades\Schema;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint violations
        Schema::disableForeignKeyConstraints();
        // Truncate the table
        DB::table('products')->truncate(); 
        // Enable foreign key checks
        Schema::enableForeignKeyConstraints();
        

        // Load the CSV document from a file path
        $csv = Reader::createFromPath(storage_path('app/database_data/products_upd.csv'), 'r');
        $csv->setHeaderOffset(0); // Set the CSV header offset

        $records = $csv->getRecords(); // Get all the records

        foreach ($records as $record) {
            DB::table('products')->insert([
                'bls_code' => $record['bls_code'], 
                'name' => $record['name'], 
                'product_category_id' => $record['product_category_id'] !== '' ? (int) $record['product_category_id'] : null,
                'price' => $record['price'] !== '' ? (float) $record['price'] : 0, 
                'kilocalories' => $record['kilocalories'] !== '' ? (float) $record['kilocalories'] : null

            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->truncate(); // Clear the table

        // Load the CSV document from a file path
        $csv = Reader::createFromPath(storage_path('app/database-data/products_upd.csv'), 'r');
        $csv->setHeaderOffset(0); // Set the CSV header offset

        $records = $csv->getRecords(); // Get all the records

        foreach ($records as $record) {
            DB::table('products')->insert([
                'bls_code' => $record['bls_code'],
                'name' => $record['name'],
                'product_category_id' => $record['product_category_id'] !== '' ? $record['product_category_id'] : null,
                // 'product_category_code' => $record['product_category_code'] !== '' ? $record['product_category_code'] : null,
                'price' => $record['price'] !== '' ? $record['price'] : null,
                'protein' => $record['protein'] !== '' ? $record['protein'] : null,
                'fat' => $record['fat'] !== '' ? $record['fat'] : null,
                'carbohydrate' => $record['carbohydrate'] !== '' ? $record['carbohydrate'] : null,
                'fiber' => $record['fiber'] !== '' ? $record['fiber'] : null,
                'total_sugar' => $record['total_sugar'] !== '' ? $record['total_sugar'] : null,
                'saturated_fat' => $record['saturated_fat'] !== '' ? $record['saturated_fat'] : null,
                'kilocalories' => $record['kilocalories'] !== '' ? $record['kilocalories'] : null,
                'kilocalories_with_fiber' => $record['kilocalories_with_fiber'] !== '' ? $record['kilocalories_with_fiber'] : null,
            ]);
        }
    }
}

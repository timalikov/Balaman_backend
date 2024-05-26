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
        Schema::disableForeignKeyConstraints();
        DB::table('products')->truncate(); 
        Schema::enableForeignKeyConstraints();
        

        $csv = Reader::createFromPath(storage_path('app/db_unicef/products_upd.csv'), 'r');
        $csv->setHeaderOffset(0); 

        $records = $csv->getRecords(); 

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

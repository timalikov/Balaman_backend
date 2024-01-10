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
        // Load the CSV document from a file path
        $csv = Reader::createFromPath(storage_path('app/db-seeds/products.csv'), 'r');
        $csv->setHeaderOffset(0); //set the CSV header offset

        $records = $csv->getRecords(); //get all the records

        foreach ($records as $record) {
            DB::table('products')->insert([
                'bls_code'                     => $record['bls_code'],
                'name'                         => $record['name'],
                'description'                  => $record['description'],
                'product_category_id'          => $record['product_category_id'],
                'product_category_code'        => $record['product_category_code'],
                'product_subcategory_id'       => $record['product_subcategory_id'],
                'product_subsubcategory_id'    => $record['product_subsubcategory_id'],
                'price'                        => $record['price'],
                'protein'                      => $record['protein'],
                'fat'                          => $record['fat'],
                'carbohydrate'                 => $record['carbohydrate'],
                'fiber'                        => $record['fiber'],
                'total_sugar'                  => $record['total_sugar'],
                'saturated_fat'                => $record['saturated_fat'],
                'kilocaries'                   => $record['kilocaries'],
                'kilocaries_with_fiber'        => $record['kilocaries_with_fiber']
            ]);
        }
    }
}

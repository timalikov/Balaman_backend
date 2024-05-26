<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NutrientLossesByProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Log::info('Starting NutrientLossesByProductsTableSeeder');

        // Disable foreign key checks to avoid constraint violations
        Schema::disableForeignKeyConstraints();
        // Truncate the table
        DB::table('nutrient_losses_by_products')->truncate();
        // Enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $csvDirectory = storage_path('app/db_unicef/factors/nutrient_losses/csv_data');
        $csvFiles = glob($csvDirectory . '/*.csv');
        
        if (empty($csvFiles)) {
            Log::warning('No CSV files found in directory: ' . $csvDirectory);
            return;
        }

        $insertedRecords = [];
        $rejectedRecords = [];

        foreach ($csvFiles as $csvFile) {
            Log::info('Processing file: ' . $csvFile);

            $csv = Reader::createFromPath($csvFile, 'r');
            $csv->setHeaderOffset(0);

            foreach ($csv->getRecords() as $record) {
                if ($this->isValidNumber($record['coefficient'])) {
                    try {
                        DB::table('nutrient_losses_by_products')->insert([
                            'product_id' => (int) $record['product_id'],
                            'factor_id'   => (int) $record['factor_id'],
                            'nutrient_id' => (int) $record['nutrient_id'],
                            'coefficient' => (float) $record['coefficient'],
                            'created_at'  => now(),
                        ]);

                        $insertedRecords[] = $record;
                    } catch (\Exception $e) {
                        Log::error('Error inserting record: ' . $e->getMessage());
                    }
                } else {
                    $rejectedRecords[] = $record;
                    Log::info('Record rejected due to invalid coefficient value', $record);
                }
            }
        }

        // Log or output the results
        Log::info('Inserted Records:', $insertedRecords);
        Log::info('Rejected Records:', $rejectedRecords);

        // Optionally, you can output directly to the console
        // echo "Inserted Records:\n";
        // print_r($insertedRecords);
        // echo "Rejected Records:\n";
        // print_r($rejectedRecords);
    }

    protected function getFactorIdFromFilename($filename)
    {
        return (int) basename($filename, '.csv');
    }

    protected function isValidNumber($value)
    {
        $isValid = is_numeric($value) && $value !== '-';
        if (!$isValid) {
            Log::info("Value `{$value}` is not a valid number.");
        }
        return $isValid;    
    }
}

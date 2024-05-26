<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WeightLossesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks to avoid constraint violations
        Schema::disableForeignKeyConstraints();
        // Truncate the table
        DB::table('weight_losses')->truncate();
        // Enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $csvDirectory = storage_path('app/db_unicef/factors/csv_data');
        $csvFiles = glob($csvDirectory . '/*.csv');

        $insertedRecords = [];
        $rejectedRecords = [];

        foreach ($csvFiles as $csvFile) {

            $csv = Reader::createFromPath($csvFile, 'r');
            $csv->setHeaderOffset(0);

            foreach ($csv->getRecords() as $record) {
                if ($this->isValidNumber($record['coefficient'])) {
                    DB::table('weight_losses')->insert([
                        'product_id'  => (int) $record['product_id'],
                        'factor_id'   => (int) $record['factor_id'],
                        'coefficient' => (float) $record['coefficient'],
                        'created_at'  => now(),
                    ]);

                    $insertedRecords[] = $record;
                } else {
                    $rejectedRecords[] = $record;
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
        return is_numeric($value) && $value !== '-';
    }
}

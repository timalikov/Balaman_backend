<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FactorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $factors = [
            ['name' => 'Холодная обработка'],
            ['name' => 'Жарка на сковороде с небольшим количеством масла'],
            ['name' => 'Тушение'],
            ['name' => 'Приготовление на пару'],
            ['name' => 'Варка'],
            ['name' => 'Запекание/выпечка'],
            ['name' => 'Жарка на сухой сковороде'],
            ['name' => 'Медленная варка'],
            ['name' => 'Бланширование']
        ];

        foreach ($factors as $factor) {
            DB::table('factors')->insert($factor);
        }
    }
}

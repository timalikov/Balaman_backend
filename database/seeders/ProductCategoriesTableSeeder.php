<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $categories = [
            "Гарниры" => "GAR",
            "Мясные блюда" => "MT",
            "Каши и зерновые" => "POR",
            "Изделия из теста" => "DGH",
            "Напитки" => "BEV",
            "Сложные мясные блюда" => "CMT",
            "Супы" => "SP",
            "Салаты" => "SAL",
            "Блюда из творога" => "CC",
            "Десерты" => "DES",
            "Блюда из яиц" => "EGG",
            "Фрукты и ягоды" => "FR",
            "Сухофрукты" => "DFR",
            "Сыры и масла" => "CH",
            "Закуски" => "APP",
            "Рыба и морепродукты" => "FSH",
            "Сложные рыбные блюда" => "CFSH",
            "Соусы" => "SAU",
            "Перекусы" => "SN",
            "Сложные вегетарианские блюда" => "VEG",
            "На завтрак" => "BR",
            "Другое" => "OTHER"
        ];

        foreach ($categories as $name => $code) {
            if (DB::table('product_categories')->where('code', $code)->doesntExist()) {
                DB::table('product_categories')->insert([
                    'name' => $name,
                    'code' => $code
                ]);
            }
            
        }
    }
}

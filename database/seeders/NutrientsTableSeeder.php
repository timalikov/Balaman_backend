<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NutrientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint violations
        Schema::disableForeignKeyConstraints();
        // Truncate the table
        DB::table('nutrients')->truncate();
        // Enable foreign key checks
        Schema::enableForeignKeyConstraints();


        $nutrients = [
            ['name' => 'water', 'measurement_unit' => 'l'],
            ['name' => 'protein', 'measurement_unit' => 'g'],
            ['name' => 'fat', 'measurement_unit' => 'g'],
            ['name' => 'carbohydrate', 'measurement_unit' => 'g'],
            ['name' => 'Органические кислоты', 'measurement_unit' => 'mg'],
            ['name' => 'vitaminA', 'measurement_unit' => 'µg'],
            ['name' => 'vitaminD', 'measurement_unit' => 'µg'],
            ['name' => 'vitaminE', 'measurement_unit' => 'mg'],
            ['name' => 'vitaminK', 'measurement_unit' => 'µg'],
            ['name' => 'vitaminB1', 'measurement_unit' => 'mg'],
            ['name' => 'vitaminB2', 'measurement_unit' => 'mg'],
            ['name' => 'vitaminB3', 'measurement_unit' => 'mg'],
            ['name' => 'vitaminB5', 'measurement_unit' => 'mg'],
            ['name' => 'vitaminB6', 'measurement_unit' => 'mg'],
            ['name' => 'vitaminB7', 'measurement_unit' => 'µg'],
            ['name' => 'vitaminB9', 'measurement_unit' => 'µg'],
            ['name' => 'vitaminB12', 'measurement_unit' => 'µg'],
            ['name' => 'vitaminC', 'measurement_unit' => 'mg'],
            ['name' => 'Пищевые волокна', 'measurement_unit' => 'g'],
            ['name' => 'potassium', 'measurement_unit' => 'mg'],
            ['name' => 'calcium', 'measurement_unit' => 'mg'],
            ['name' => 'magnesium', 'measurement_unit' => 'mg'],
            ['name' => 'phosphorus', 'measurement_unit' => 'mg'],
            ['name' => 'Хлорид', 'measurement_unit' => 'mg'],
            ['name' => 'iron', 'measurement_unit' => 'mg'],
            ['name' => 'zinc', 'measurement_unit' => 'mg'],
            ['name' => 'copper', 'measurement_unit' => 'µg'],
            ['name' => 'Марганец', 'measurement_unit' => 'mg'],
            ['name' => 'iodine', 'measurement_unit' => 'µg'],
            ['name' => 'Сахароза (свекольный сахар)', 'measurement_unit' => 'g'],
            ['name' => 'sodium', 'measurement_unit' => 'g'],
            ['name' => 'Сахар (общий)', 'measurement_unit' => 'g'],
            ['name' => 'Водорастворимая клетчатка', 'measurement_unit' => 'g'],
            ['name' => 'Нерастворимая в воде клетчатка', 'measurement_unit' => 'g'],
            ['name' => 'Изолейцин', 'measurement_unit' => 'mg'],
            ['name' => 'Лейцин', 'measurement_unit' => 'mg'],
            ['name' => 'Лизин', 'measurement_unit' => 'mg'],
            ['name' => 'Метионин', 'measurement_unit' => 'mg'],
            ['name' => 'Цистеин', 'measurement_unit' => 'mg'],
            ['name' => 'Фенилаланин', 'measurement_unit' => 'mg'],
            ['name' => 'Тирозин', 'measurement_unit' => 'mg'],
            ['name' => 'Треонин', 'measurement_unit' => 'mg'],
            ['name' => 'Триптофан', 'measurement_unit' => 'mg'],
            ['name' => 'Валин', 'measurement_unit' => 'mg'],
            ['name' => 'Аргинин', 'measurement_unit' => 'mg'],
            ['name' => 'Гистидин', 'measurement_unit' => 'mg'],
            ['name' => 'Незаменимые аминокислоты', 'measurement_unit' => 'mg'],
            ['name' => 'Аланин', 'measurement_unit' => 'mg'],
            ['name' => 'Аспарагиновая кислота', 'measurement_unit' => 'mg'],
            ['name' => 'Глутаминовая кислота', 'measurement_unit' => 'mg'],
            ['name' => 'Глицин', 'measurement_unit' => 'mg'],
            ['name' => 'Пролин', 'measurement_unit' => 'mg'],
            ['name' => 'Серин', 'measurement_unit' => 'mg'],
            ['name' => 'Насыщенные жирные кислоты', 'measurement_unit' => 'g'],
            ['name' => 'Мононенасыщенные жирные кислоты', 'measurement_unit' => 'g'],
            ['name' => 'Октадекатриеновая кислота / линоленовая кислота', 'measurement_unit' => 'g'],
            ['name' => 'Эйкозапентаеновая кислота', 'measurement_unit' => 'g'],
            ['name' => 'Докозагексаеновая кислота', 'measurement_unit' => 'g'],
            ['name' => 'Полиненасыщенные жирные кислоты', 'measurement_unit' => 'g'],
            ['name' => 'Омега-3 жирные кислоты', 'measurement_unit' => 'g'],
            ['name' => 'Омега-6 жирные кислоты', 'measurement_unit' => 'g'],
            ['name' => 'Холестерин', 'measurement_unit' => 'g'],
            ['name' => 'Соотношение полиненасыщенных и насыщенных жиров (P / S)', 'measurement_unit' => 'mg'],
            ['name' => 'Хлебные единицы', 'measurement_unit' => 'mg'],
            ['name' => 'Поваренная соль всего', 'measurement_unit' => 'g'],
            ['name' => 'Селен', 'measurement_unit' => 'mg'],
            ['name' => 'TFA, трансжирные кислоты', 'measurement_unit' => 'g'],
            ['name' => 'Криптоксантин', 'measurement_unit' => 'mg'],
            ['name' => 'Лютеин', 'measurement_unit' => 'mg'],
            ['name' => 'Ликопин', 'measurement_unit' => 'mg'],
        ];
        
        
        foreach ($nutrients as $nutrient) {
            DB::table('nutrients')->insert([
                'name' => $nutrient['name'],
                'measurement_unit' => $nutrient['measurement_unit'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

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
            ['name' => 'Вода', 'measurement_unit' => 'mg'],
            ['name' => 'Яичный белок (белок)', 'measurement_unit' => 'g'],
            ['name' => 'Жиры', 'measurement_unit' => 'g'],
            ['name' => 'Углеводы, абсорбируемые', 'measurement_unit' => 'g'],
            ['name' => 'Органические кислоты', 'measurement_unit' => 'mg'],
            ['name' => 'Эквивалент ретинола витамина А', 'measurement_unit' => 'µg'],
            ['name' => 'Кальциферолы витамина D', 'measurement_unit' => 'µg'],
            ['name' => 'Эквивалент альфа-токоферола витамина Е', 'measurement_unit' => 'mg'],
            ['name' => 'Филлохинон витамина К', 'measurement_unit' => 'µg'],
            ['name' => 'Витамин B1 тиамин', 'measurement_unit' => 'mg'],
            ['name' => 'Витамин В2 рибофлавин', 'measurement_unit' => 'mg'],
            ['name' => 'Витамин B3 в эквиваленте ниацина', 'measurement_unit' => 'mg'],
            ['name' => 'Витамин B5 пантотеновая кислота', 'measurement_unit' => 'mg'],
            ['name' => 'Пиридоксин витамина B6', 'measurement_unit' => 'mg'],
            ['name' => 'Витамин B7, биотин (витамин H)', 'measurement_unit' => 'µg'],
            ['name' => 'Витамин B9 общий фолиевая кислота', 'measurement_unit' => 'µg'],
            ['name' => 'Витамин B12 кобаламин', 'measurement_unit' => 'µg'],
            ['name' => 'Витамин С аскорбиновая кислота', 'measurement_unit' => 'mg'],
            ['name' => 'Пищевые волокна', 'measurement_unit' => 'g'],
            ['name' => 'Калий', 'measurement_unit' => 'mg'],
            ['name' => 'Кальций', 'measurement_unit' => 'mg'],
            ['name' => 'Магний', 'measurement_unit' => 'mg'],
            ['name' => 'Фосфор', 'measurement_unit' => 'mg'],
            ['name' => 'Хлорид', 'measurement_unit' => 'mg'],
            ['name' => 'Железо', 'measurement_unit' => 'mg'],
            ['name' => 'Цинк', 'measurement_unit' => 'mg'],
            ['name' => 'Медь', 'measurement_unit' => 'µg'],
            ['name' => 'Марганец', 'measurement_unit' => 'mg'],
            ['name' => 'Йодид', 'measurement_unit' => 'µg'],
            ['name' => 'Сахароза (свекольный сахар)', 'measurement_unit' => 'g'],
            ['name' => 'Натрий', 'measurement_unit' => 'g'],
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

<?php

namespace App\Services;
use App\Models\Product;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Cell;
use Illuminate\Support\Facades\Log;
use App\Models\Menu;


class MenuLayoutGenerationService{
    public function generateMenuLayout($request)
    {
        $phpWord = new PhpWord();

        $titleFontStyle = array('name' => 'Arial', 'size' => 14, 'bold' => true);
        $headerFontStyle = array('name' => 'Arial', 'size' => 12);

        $styleTable = array('borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 80);

        $section = $phpWord->addSection();

        $menuId = $request->input('menu_id');  
        $menu = Menu::findOrFail($menuId);  
        $totalPrice = 0;

        $selectedDayNumber = $request->input('day_number'); 
        $selectedWeekNumber = $request->input('week_number');

        $meals = $menu->menuMealTimes()->where([
            ['day_of_week', '=', $selectedDayNumber],
            ['week', '=', $selectedWeekNumber],
        ])->with(['mealDishes'])->get();  
        
        $children_count = $request->input('children_count');

        $year = date('Y');
        $section->addText('Меню раскладка на ' . $menu->menu_id . " " . $year, $titleFontStyle, array('alignment' => 'center'));
        $section->addText('Общее количество детей: ' . $children_count, $headerFontStyle, array('alignment' => 'center'));
        $section->addTextBreak(1);

        foreach ($meals as $meal) {
            $table = $section->addTable($styleTable);
            $table->addRow();
            $table->addCell(2000)->addText($meal->meal_time_name, $headerFontStyle);

            $table->addCell(1000)->addText('Выход', $headerFontStyle);
            $table->addCell(1000)->addText('Чел.: ' . $children_count);
            $table->addCell(1000)->addText('Цена', $headerFontStyle);
       
            foreach ($meal->mealDishes as $dish) {
                $table->addRow();
                $table->addCell(2000)->addText($dish->name);
                $table->addCell(1000)->addText($dish->weight);
                $table->addCell(1000)->addText($dish->weight * $children_count);
                $table->addCell(1000)->addText($dish->price * $children_count);
                $totalPrice += $dish->price * $children_count;

            }            
            $section->addText('Итого: ' . $totalPrice . ' тенге', $headerFontStyle, array('alignment' => 'right'));
            $section->addTextBreak(1);
        }
        

        $fileName = 'menu_doc11.docx';
        try {
            $phpWord->save($fileName, 'Word2007', true);
        } catch (\Exception $e) {
            Log::error("Error generating document: " . $e->getMessage());
            return response()->json(['error' => 'Failed to generate document'], 500);
        }

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Access-Control-Allow-Origin' => '*', // CORS Header
        ];

        return response()->download($fileName, $fileName, $headers)->deleteFileAfterSend(true);

    }
}
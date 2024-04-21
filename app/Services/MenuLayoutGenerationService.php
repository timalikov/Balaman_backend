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

        // table style
        $styleTable = array('borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 80);


        // Set the document to landscape orientation
        $section = $phpWord->addSection(['orientation' => 'landscape']);

        $menuId = $request->input('menu_id');  // Assume the menu ID is passed as part of the request
        $menu = Menu::findOrFail($menuId);  // Retrieve the menu or fail if not found

        $selectedDayNumber = $request->input('day_number');  // Day number input
        $selectedWeekNumber = $request->input('week_number');  // Week number input

        // Fetch data for the selected day and week within the identified menu
        $meals = $menu->menuMealTimes()->where([
            ['day_of_week', '=', $selectedDayNumber],
            ['week', '=', $selectedWeekNumber],
        ])->with(['mealTime', 'mealDishes'])->get();  
        
        $children_count = $request->input('children_count');

        $year = date('Y');
        $section->addText('Меню раскладка на ' . $menu->menu_id . " " . $year, $titleFontStyle, array('alignment' => 'center'));
        $section->addText('Общее количество детей: ' . $children_count, $headerFontStyle, array('alignment' => 'center'));
        $section->addTextBreak(1);

        foreach ($meals as $meal) {
            $table = $section->addTable($styleTable);
            $table->addRow();
            $table->addCell(2000)->addText($meal->mealTime->name, $headerFontStyle);
            $table->addCell(1000)->addText('Чел.', $headerFontStyle);
            $table->addCell(1000)->addText('Выход', $headerFontStyle);
            $table->addCell(2000)->addText('Наименование продуктов', $headerFontStyle);
       
            foreach ($meal->mealDishes as $dish) {
                $table->addRow();
                $table->addCell(2000)->addText($dish->name);
                $table->addCell(1000)->addText($children_count);
                $table->addCell(1000)->addText($dish->weight);

                
                // iterate through the products of the dish
                $products = $dish->products;
                foreach ($products as $product) {
                    $table->addCell(2000)->addText($product->name . " - " . $product->pivot->weight);
                }
            }            
            $section->addTextBreak(1);
        }
        

        // Save the file
        $fileName = 'меню_раскладка.docx';
        try {
            // Your existing code to generate the document
            $phpWord->save($fileName, 'Word2007', true);
        } catch (\Exception $e) {
            Log::error("Error generating document: " . $e->getMessage());
            return response()->json(['error' => 'Failed to generate document'], 500);
        }

        // Return the file
        return response()->download($fileName)->deleteFileAfterSend(true);

    }
}
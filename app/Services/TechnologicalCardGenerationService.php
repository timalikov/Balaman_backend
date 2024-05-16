<?php
// promt:Great! How can I using this information about Products create data, with showing info about each product, its nutrients with, its factor,


namespace App\Services;
use App\Models\Product;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Cell;
use Illuminate\Support\Facades\Log;

class TechnologicalCardGenerationService{


    public function generateTechnologicalCard($name, $description, $products)
    {

        $totals = [
            'protein' => 0,
            'fat' => 0,
            'carbohydrate' => 0,
            'kilocalories' => 0,
            'weight' => 0,

            'vitaminA' => 0,
            'vitaminD' => 0,
            'vitaminE' => 0,
            'vitaminK' => 0,
            'vitaminB1' => 0,
            'vitaminB2' => 0,
            'vitaminB3' => 0,
            'vitaminB5' => 0,
            'vitaminB6' => 0,
            'vitaminB7' => 0,
            'vitaminB9' => 0,
            'vitaminB12' => 0,
            'vitaminC' => 0,
            'potassium' => 0,
            'calcium' => 0,
            'magnesium' => 0,
            'phosphorus' => 0,
            'iron' => 0,
            'zinc' => 0,
            'copper' => 0,
            'iodine' => 0,
            'sodium' => 0,
        ];

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $boldFontStyle = new Font();
        $boldFontStyle->setName('Arial');
        $boldFontStyle->setSize(11);
        $boldFontStyle->setBold(true);

        $boldFontStyle2 = new Font();
        $boldFontStyle2->setName('Arial');
        $boldFontStyle2->setSize(10);
        $boldFontStyle2->setBold(true);

        $underlineFontStyle = array('underline' => 'single', 'bold' => true);


        $section->addText("");
        
        $section->addText("Технологическая карта", $boldFontStyle, array('align' => 'center'));

        $section->addText("");

        $section->addText("Наименование блюда: " . (isset($name) ? $name : "___________" ), $boldFontStyle, array('align' => 'center'));


        $section->addText("");
        $section->addText("");

        $styleTable = ['borderSize' => 6, 'borderColor' => '999999'];
        $styleCell = ['valign' => 'center'];
        $styleCellBTLR = ['valign' => 'center', 'textDirection' => Cell::TEXT_DIR_BTLR];
        $fontStyle = ['bold' => true, 'align' => 'center'];

        $table = $section->addTable($styleTable);

        $table->addRow();

        $headers = ["Наименование Продукта", "Вес Брутто", "Вес Нетто", "Белки", "Жиры", "Углеводы", "Калорийность"];
        
        foreach ($headers as $header) {
            $table->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }



        foreach ($products as $productData) {
            $table->addRow();
            $table->addCell(2000)->addText($productData['name'] ?? '');
            $table->addCell(2000)->addText($productData['brutto_weight'] ?? ''); 
            $table->addCell(2000)->addText($productData['weight']); 
            $totals['weight'] += $productData['weight'];

            $proteinValue = $fatValue = $carbohydrateValue = 0;

            $nutrientNames = ['protein', 'fat', 'carbohydrate', 'vitaminA', 'vitaminD', 'vitaminE', 'vitaminK', 'vitaminB1', 'vitaminB2', 'vitaminB3', 'vitaminB5', 'vitaminB6', 'vitaminB7', 'vitaminB9', 'vitaminB12', 'vitaminC', 'potassium', 'calcium', 'magnesium', 'phosphorus', 'iron', 'zinc', 'copper', 'iodine', 'sodium'];

            foreach ($productData['nutrients'] as $nutrient) {
                foreach ($nutrientNames as $nutrientName) {
                    if ($nutrient['name'] == $nutrientName) {
                        $totals[$nutrientName] += $nutrient['pivot']['weight'];
                        if ($nutrientName == 'protein') {
                            $proteinValue = $nutrient['pivot']['weight'];
                        } elseif ($nutrientName == 'fat') {
                            $fatValue = $nutrient['pivot']['weight'];
                        } elseif ($nutrientName == 'carbohydrate') {
                            $carbohydrateValue = $nutrient['pivot']['weight'];
                        }
                    }
                }
            }

            $table->addCell(2000)->addText($proteinValue ?? ''); 

            $table->addCell(2000)->addText($fatValue ?? ''); 

            $table->addCell(2000)->addText($carbohydrateValue ?? '' ); 

            if (isset($productData['kilocalories'])) {
                $table->addCell(2000)->addText($productData['kilocalories']);
                $totals['kilocalories'] += $productData['kilocalories'];
            } else {
                $table->addCell(2000)->addText('');
            }
            
        }

        $table->addRow();
        $table->addCell(2000)->addText("Итого");
        $table->addCell(2000)->addText("");
        $table->addCell(2000)->addText($totals['weight'] ?? '');
        $table->addCell(2000)->addText($totals['protein'] ?? '');
        $table->addCell(2000)->addText($totals['fat'] ?? '');
        $table->addCell(2000)->addText($totals['carbohydrate'] ?? '');
        $table->addCell(2000)->addText($totals['kilocalories'] ?? '');
                
        
        
        $section->addText("_________________________________________________________________________________", [], ['align' => 'center']);
        $section->addText("");
        $section->addText("Описание рецепта:" . (isset($description) ? $description : '_________________________________________________________________________________'), $underlineFontStyle);
        $section->addText("");
        $section->addText("_________________________________________________________________________________", [], ['align' => 'center']);
        $section->addText("");
        $section->addText("");
        $section->addText("");

        $table2 = $section->addTable($styleTable);

        $table2->addRow();

        $vitaminHeaders = ["A", "D", "E", "K", "B1", "B2", "B3"];
        $vitaminHeaders2 = ["B5", "B6", "B7", "B9", "B12", "C"];

        $table2->addCell(1750, $styleCell)->addText("Витамины", $boldFontStyle2);
        foreach ($vitaminHeaders as $header) {
            $table2->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }
        
        $table2->addRow();
        $table2->addCell(1750)->addText("");
        foreach ($vitaminHeaders as $header) {
            $nutrientName = 'vitamin' . $header;
            $table2->addCell(1750, $styleCell)->addText($totals[$nutrientName] ?? '');
        }

        $table2->addRow();
        $table2->addCell(1750)->addText("", $boldFontStyle2);
        foreach ($vitaminHeaders2 as $header) {
            $table2->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }

        $table2->addRow();
        $table2->addCell(1750)->addText("");
        foreach ($vitaminHeaders2 as $header) {
            $nutrientName = 'vitamin' . $header;
            $table2->addCell(1750, $styleCell)->addText($totals[$nutrientName] ?? '');
        }


        $mineralHeaders = ["potassium", "calcium", "magnesium", "phosphorus", "iron", "zinc", "copper"];
        $mineralHeadersInRussian = ["Калий", "Кальций", "Магний", "Фосфор", "Железо", "Цинк", "Медь"];
        $mineralHeaders2 = [ "iodine", "sodium"];
        $mineralHeadersInRussian2 = ["Йод", "Натрий"];

        $table2->addRow();
        $table2->addCell(1750)->addText("Минералы", $boldFontStyle2);
        foreach ($mineralHeadersInRussian as $header) {
            $table2->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }

        $table2->addRow();
        $table2->addCell(1750)->addText("");
        foreach ($mineralHeaders as $header) {
            $table2->addCell(1750, $styleCell)->addText($totals[$header] ?? '');
        }

        $table2->addRow();
        $table2->addCell(1750)->addText("", $boldFontStyle2);
        foreach ($mineralHeadersInRussian2 as $header) {
            $table2->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }

        $table2->addRow();
        $table2->addCell(1750)->addText("");
        foreach ($mineralHeaders2 as $header) {
            $table2->addCell(1750, $styleCell)->addText($totals[$header] ?? '');
        }




        $fileName = 'technological_card.docx';
        try {
            $phpWord->save($fileName, 'Word2007', true);
        } catch (\Exception $e) {
            Log::error("Error generating document: " . $e->getMessage());
            return response()->json(['error' => 'Failed to generate document'], 500);
        }
        
        return response()->download($fileName)->deleteFileAfterSend(true);

    }
}
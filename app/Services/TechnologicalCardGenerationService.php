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

        // Log::info('Generating technological card');
        // Log::info($products);
        
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

        // Create a bold font style object
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

        

         // Add a new section
        // $section = $phpWord->addSection();

        $section->addText("");
        $section->addText("");

        // Define the style for the table
        $styleTable = ['borderSize' => 6, 'borderColor' => '999999'];
        $styleCell = ['valign' => 'center'];
        $styleCellBTLR = ['valign' => 'center', 'textDirection' => Cell::TEXT_DIR_BTLR];
        $fontStyle = ['bold' => true, 'align' => 'center'];

        // Add table to the section
        $table = $section->addTable($styleTable);

        // Add the header row
        $table->addRow();

        // Define the headers
        $headers = ["Наименование Продукта", "Вес Брутто", "Вес Нетто", "Белки", "Жиры", "Углеводы", "Калорийность"];
        
        foreach ($headers as $header) {
            $table->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }



        // Iterate over products and add a row for each
        foreach ($products as $productData) {
            $table->addRow();
            $table->addCell(2000)->addText($productData['name'] ?? '');
            $table->addCell(2000)->addText($productData['brutto_weight'] ?? ''); // Assuming 'weight' is the gross weight
            //net weight
            $table->addCell(2000)->addText($productData['weight']); // Assuming 'weight' is the gross weight
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

        // add totals to the table
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

        // All vitamins separated into 2 rows
        $vitaminHeaders = ["A", "D", "E", "K", "B1", "B2", "B3"];
        $vitaminHeaders2 = ["B5", "B6", "B7", "B9", "B12", "C"];

        // First row Headers
        $table2->addCell(1750, $styleCell)->addText("Витамины", $boldFontStyle2);
        foreach ($vitaminHeaders as $header) {
            $table2->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }
        
        // Fill the first row's values (create second row)
        $table2->addRow();
        $table2->addCell(1750)->addText("");
        foreach ($vitaminHeaders as $header) {
            $nutrientName = 'vitamin' . $header;
            $table2->addCell(1750, $styleCell)->addText($totals[$nutrientName] ?? '');
        }

        // Second row Headers
        $table2->addRow();
        $table2->addCell(1750)->addText("", $boldFontStyle2);
        foreach ($vitaminHeaders2 as $header) {
            $table2->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }

        // Fill the second row's values (create third row)
        $table2->addRow();
        $table2->addCell(1750)->addText("");
        foreach ($vitaminHeaders2 as $header) {
            $nutrientName = 'vitamin' . $header;
            $table2->addCell(1750, $styleCell)->addText($totals[$nutrientName] ?? '');
        }


        // All minerals separated into 2 rows
        $mineralHeaders = ["potassium", "calcium", "magnesium", "phosphorus", "iron", "zinc", "copper"];
        $mineralHeadersInRussian = ["Калий", "Кальций", "Магний", "Фосфор", "Железо", "Цинк", "Медь"];
        $mineralHeaders2 = [ "iodine", "sodium"];
        $mineralHeadersInRussian2 = ["Йод", "Натрий"];

        // First row Headers
        $table2->addRow();
        $table2->addCell(1750)->addText("Минералы", $boldFontStyle2);
        foreach ($mineralHeadersInRussian as $header) {
            $table2->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }

        // Fill the first row's values (create second row)
        $table2->addRow();
        $table2->addCell(1750)->addText("");
        foreach ($mineralHeaders as $header) {
            $table2->addCell(1750, $styleCell)->addText($totals[$header] ?? '');
        }

        // Second row Headers
        $table2->addRow();
        $table2->addCell(1750)->addText("", $boldFontStyle2);
        foreach ($mineralHeadersInRussian2 as $header) {
            $table2->addCell(1750, $styleCell)->addText($header, $boldFontStyle2);
        }

        // Fill the second row's values (create third row)
        $table2->addRow();
        $table2->addCell(1750)->addText("");
        foreach ($mineralHeaders2 as $header) {
            $table2->addCell(1750, $styleCell)->addText($totals[$header] ?? '');
        }




        // Save the file
        $fileName = 'technological_card.docx';
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
<?php
// promt:Great! How can I using this information about Products create data, with showing info about each product, its nutrients with, its factor,


namespace App\Services;
use App\Models\Product;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Cell;

class TechnologicalCardGeneratorService{


    public function generateTechnologicalCard($products)
    {
        $totals = [
            'protein' => 0,
            'fat' => 0,
            'carbohydrate' => 0,
            'kilocalories' => 0,
            'weight' => 0
        ];

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Create a bold font style object
        $boldFontStyle = new Font();
        $boldFontStyle->setName('Arial');
        $boldFontStyle->setSize(11);
        $boldFontStyle->setBold(true);

        $section->addText("");
        
        $section->addText("Технологическая карта", $boldFontStyle, array('align' => 'center'));

        $section->addText("");

        $section->addText("Наименование блюда _________________", $boldFontStyle, array('align' => 'center'));
        

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
            $table->addCell(1750, $styleCell)->addText($header, $fontStyle);
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

            foreach ($productData['nutrients'] as $nutrient) {
                if ($nutrient['nutrient_id'] == 70) { // Protein
                    $proteinValue = $nutrient['pivot']['weight'];
                    $totals['protein'] += $proteinValue;
                } elseif ($nutrient['nutrient_id'] == 21) { // Fat
                    $fatValue = $nutrient['pivot']['weight'];
                    $totals['fat'] += $fatValue;
                } elseif ($nutrient['nutrient_id'] == 58) { // Carbohydrate
                    $carbohydrateValue = $nutrient['pivot']['weight'];
                    $totals['carbohydrate'] += $carbohydrateValue;
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




        // Save the file
        $fileName = 'technological_card.docx';
        $phpWord->save($fileName, 'Word2007', true);

        // Return the file
        return response()->download($fileName)->deleteFileAfterSend(true);

    }
}
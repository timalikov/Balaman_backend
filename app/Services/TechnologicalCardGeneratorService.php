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
            'kilocaries' => 0,
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
            // Find the product model
            $product = Product::with('factors')->find($productData['product_id']);

            // Get the selected factor IDs for this product
            $selectedFactorIds = $productData['factor_id'] ?? [];

            // Initialize a variable to hold the total coefficient of loss
            $totalCoefficientOfLoss = 1;

            // Iterate over the selected factor IDs and accumulate the coefficients
            foreach ($selectedFactorIds as $factorId) {
                $factor = $product->factors->firstWhere('factor_id', $factorId);
                if ($factor) {
                    $totalCoefficientOfLoss *= $factor->pivot->coefficient;
                }
            }

            
            $table->addRow();
            $table->addCell(2000)->addText($product['name'] ?? '');
            $table->addCell(2000)->addText($product['weight'] ?? ''); // Assuming 'weight' is the gross weight
            //net weight
            $table->addCell(2000)->addText($product['weight'] * $totalCoefficientOfLoss ?? ''); // Assuming 'weight' is the gross weight
            $totals['weight'] += $product['weight'] * $totalCoefficientOfLoss ?? '';

            $table->addCell(2000)->addText($product['protein'] * $totalCoefficientOfLoss ?? ''); 
            $totals['protein'] += $product['protein'] * $totalCoefficientOfLoss ?? '';

            $table->addCell(2000)->addText($product['fat'] * $totalCoefficientOfLoss ?? ''); 
            $totals['fat'] += $product['fat'] * $totalCoefficientOfLoss ?? '';

            $table->addCell(2000)->addText($product['carbohydrate'] * $totalCoefficientOfLoss ?? ''); 
            $totals['carbohydrate'] += $product['carbohydrate'] * $totalCoefficientOfLoss ?? '';

            $table->addCell(2000)->addText($product['kilocaries'] * $totalCoefficientOfLoss ?? ''); 
            $totals['kilocaries'] += $product['kilocaries'] * $totalCoefficientOfLoss ?? '';
            
        }



        // add totals to the table
        $table->addRow();
        $table->addCell(2000)->addText("Итого");
        $table->addCell(2000)->addText("");
        $table->addCell(2000)->addText($totals['weight'] ?? '');
        $table->addCell(2000)->addText($totals['protein'] ?? '');
        $table->addCell(2000)->addText($totals['fat'] ?? '');
        $table->addCell(2000)->addText($totals['carbohydrate'] ?? '');
        $table->addCell(2000)->addText($totals['kilocaries'] ?? '');




        // Save the file
        $fileName = 'technological_card.docx';
        $phpWord->save($fileName, 'Word2007', true);

        // Return the file
        return response()->download($fileName)->deleteFileAfterSend(true);

    }
}
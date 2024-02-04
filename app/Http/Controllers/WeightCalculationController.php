<?php
// This controller used for testing purposes and not included in any endpoint logic

namespace App\Http\Controllers;

use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;
use App\Services\HttpRequestService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class WeightCalculationController extends Controller
{
    protected $weightCalculationService;
    protected $productFetchService;

    public function __construct(WeightCalculationService $weightCalculationService, ProductFetchService $productFetchService)
    {
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;

    }

    public function process(Request $request)
    {
        // $products = $request->input('products');       

        // // Validate that products is not null and is an array
        // if (is_null($products) || !is_array($products)) {
        //     return response()->json(['error' => 'Invalid products data'], 400);
        // }

        // $processedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);
        // $processedProducts = $this->productFetchService->completeProductRequest($request);
        
        // return $processedProducts;

        $response = "hello";

        return $response;
    }
}

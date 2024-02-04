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
    protected $httpRequestService;

    public function __construct(WeightCalculationService $weightCalculationService, ProductFetchService $productFetchService, HttpRequestService $httpRequestService)
    {
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;
        $this->httpRequestService = $httpRequestService;

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

        Log::info('Processing /api/factors request');

        $url = 'http://127.0.0.1:8000/api/factors';
        $data = ['name' => 'value'];


        $response = Http::timeout(60)->post('http://127.0.0.1:8000/api/factors', [
            'name' => 'Example Title'
        ]);
        Log::info('Completed /api/factors request');

        

        return $response;
    }
}

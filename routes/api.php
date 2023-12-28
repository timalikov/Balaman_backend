<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DishController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NutrientCalculationController;
use App\Http\Controllers\TechnologicalCardController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource(
    'dishes',
    DishController::class
);

Route::apiResource(
    'products',
    ProductController::class
);

Route::post('/calculate-nutrients', [NutrientCalculationController::class, 'calculate']);

// generate-technological-card

Route::post('/generate-technological-card', [TechnologicalCardController::class, 'generate']);

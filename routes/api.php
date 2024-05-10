<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DishController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NutrientCalculationController;
use App\Http\Controllers\TechnologicalCardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FactorController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\DishCategoryController;
use App\Http\Controllers\WeightCalculationController;
use App\Http\Controllers\NutrientController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\DishNutrientCalculationController;
use App\Http\Controllers\NutritionCalculatorController;
use App\Http\Controllers\MenuLayoutController;


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

Route::group(
    [
        'middleware' => 'api',
        'prefix' => 'auth'
    ],
    function ($router) {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
       
    }
);

// Public routes
Route::get('/products', [ProductController::class, 'index']); // List all products
Route::middleware('role:admin')->get('/products/{product}', [ProductController::class, 'show']); // Show a single product
// ... other routes

// Protected routes
Route::middleware('permission:all')->post('/products', [ProductController::class, 'store']);

// Route::post('/products', [ProductController::class, 'store'])->middleware('api','checkRolePermission:create');
Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('jwt.verify', 'checkRolePermission:update');
Route::delete('/products/{product}', [ProductController::class, 'delete'])->middleware('jwt.verify', 'checkRolePermission:destroy');

Route::apiResource(
    'products',
    ProductController::class
);

Route::apiResource(
    'dishes',
    DishController::class
);

Route::apiResource(
    'nutrients',
    NutrientController::class
);

Route::apiResource(
    'factors',
    FactorController::class
);

Route::apiResource(
    'product-categories',
    ProductCategoryController::class
);

Route::apiResource(
    'dish-categories',
    DishCategoryController::class
);

Route::post('/calculate-total-nutrients', [NutrientCalculationController::class, 'calculateTotalNutrients']);
Route::post('/nutrient-details', [NutrientCalculationController::class, 'calculateProductNutrientDetails']);

// generate-technological-card
Route::post('/generate-technological-card', [TechnologicalCardController::class, 'generate']);

Route::post('/calculate-weight', [WeightCalculationController::class, 'process']);


Route::delete('/menus/remove-dish', [MenuController::class, 'removeDishFromMenu']);

Route::apiResource(
    'menus',
    MenuController::class
);

Route::post('/menus/meal-plans', [MenuController::class, 'createOrUpdateMealPlan']);

Route::post('/menus/get-meal-plan', [MenuController::class, 'getMealTimesByWeekAndDay']);

Route::post('/menus/add-dish', [MenuController::class, 'addDishToMenu']);


// Dish calculator
Route::post('/dishes-calculate-total-nutrients', [DishNutrientCalculationController::class, 'getTotalNutritrientsOfDishes']);


// Menu status update
Route::post('/menus/{menu}/update-status', [MenuController::class, 'updateMenuStatus']);

// Calculate average daily nutrition
Route::post('/calculate-average-daily-nutrition', [MenuController::class, 'calculateMenuNutrition']);

// generate menu layout 
Route::post('/generate-menu-layout', [MenuLayoutController::class, 'generate']);
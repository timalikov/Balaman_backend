<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;

use App\Models\Dish;
use App\Models\DishCategory;

use Illuminate\Support\Facades\Validator;

class DishController extends Controller
{
    protected $nutrientCalculationService;
    protected $weightCalculationService;
    protected $productFetchService;


    public function __construct(NutrientCalculationService $nutrientCalculationService, WeightCalculationService $weightCalculationService, ProductFetchService $productFetchService)
    {
        $this->nutrientCalculationService = $nutrientCalculationService;
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;

    }

    /**
     * @OA\Get(
     *     path="/dishes",
     *     operationId="listDishes",
     *     tags={"Dishes"},
     *     summary="List all dishes",
     *     description="Retrieves a list of dishes with optional filtering.",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for dish name or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="dish_id",
     *         in="query",
     *         description="Filter by a specific dish ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dish_category_id",
     *         in="query",
     *         description="Filter by a specific dish category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of dishes per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="items_per_page", type="integer"),
     *             @OA\Property(property="total_items", type="integer"),
     *             @OA\Property(property="total_pages", type="integer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Dish")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable',
            'dish_id' => 'integer|nullable',
            'dish_category_id' => 'integer|nullable',
            'per_page' => 'integer|nullable',
            'page' => 'integer|nullable' 
        ]);

        if ($request->has('dish_id')) {
            return $this->show($request->input('dish_id'));
        }

        $query = Dish::with(['dishCategory' => function($query) {
                $query->select('dish_category_id', 'name');
            }])
            ->select(['dish_id', 'bls_code', 'name', 'description', 'health_factor', 'dish_category_id', 'has_relation_with_products']);


        if ($request->has('search')) {
            $searchTerm = $request->input('search');

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('dishCategory', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            });
        }

        if ($request->has('dish_category_id')) {
            $query->whereHas('dishCategory', function ($q) use ($request) {
                $q->where('dish_category_id', $request->input('dish_category_id'));
            });
        }

        $perPage = $request->input('per_page', 10); 
        $currentPage = $request->input('page', 1); 

        $dishes = $query->paginate($perPage, ['*'], 'page', $currentPage);

        return response()->json([
            'current_page' => $dishes->currentPage(),
            'items_per_page' => $dishes->perPage(),
            'total_items' => $dishes->total(),
            'total_pages' => $dishes->lastPage(),
            'data' => $dishes->items()
        ]);
    }
    

    const NUTRIENT_IDS = ['protein' => 2, 'fat' => 3, 'carbohydrate' => 4];
    /**
     * @OA\Post(
     *     path="/api/dishes",
     *     operationId="storeDish",
     *     tags={"Dishes"},
     *     summary="Create a new dish",
     *     description="Stores a new dish with optional related products and nutrient data. Note: 'nutrients' can be included if 'products' are omitted and vice versa.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data for the new dish",
     *         @OA\JsonContent(
     *             required={"bls_code", "name", "dish_category_id"},
     *             @OA\Property(property="bls_code", type="string", example="BLS001"),
     *             @OA\Property(property="name", type="string", example="Vegan Salad"),
     *             @OA\Property(property="description", type="string", example="Delicious vegan salad.", nullable=true),
     *             @OA\Property(property="recipe_description", type="string", example="Combine all ingredients.", nullable=true),
     *             @OA\Property(property="dish_category_id", type="integer", example=1),
     *             @OA\Property(property="kilocalories", type="number", format="float", example=250, nullable=true),
     *             @OA\Property(property="price", type="number", format="float", example=19.99, nullable=true),
     *             @OA\Property(property="image_url", type="string", format="url", example="http://example.com/image.jpg", nullable=true),
     *             @OA\Property(property="health_factor", type="number", format="float", example=4, nullable=true),
     *             @OA\Property(
     *                 property="nutrients",
     *                 type="array",
     *                 description="Optional nutrients data, must not be included if 'products' is present.",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"nutrient_id", "weight"},
     *                     @OA\Property(property="nutrient_id", type="integer", example=1),
     *                     @OA\Property(property="weight", type="number", format="float", example=100)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_id", "weight"},
     *                     @OA\Property(property="product_id", type="integer", example=81),
     *                     @OA\Property(property="weight", type="number", format="float", example=200),
     *                     @OA\Property(
     *                         property="factor_ids",
     *                         type="array",
     *                         @OA\Items(type="integer", example=1)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dish created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Vegan Salad"),
     *             @OA\Property(property="price", type="number", format="float", example=19.99)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation error details here")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = $this->validateStoreRequest($request);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $validatedData = $validator->validated();
        
        if ($request->has('products')) {
            $validatedData['has_relation_with_products'] = true;
            return $this->handleProductRelatedDish($validatedData, $request);
        } else {
            return $this->handleSimpleDish($validatedData, $request);
        }
    }

    protected function validateStoreRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'bls_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'weight' => 'nullable|numeric',
            'description' => 'nullable|string',
            'recipe_description' => 'nullable|string',
            'dish_category_id' => 'required|integer|exists:dish_categories,dish_category_id',
            'kilocalories' => 'sometimes|numeric',
            'protein' => 'sometimes|numeric',
            'fat' => 'sometimes|numeric',
            'carbohydrate' => 'sometimes|numeric',
            'price' => 'sometimes|numeric',
            'image_url' => 'nullable|url',
            'health_factor' => 'nullable|numeric',
            'nutrients' => 'sometimes|array',
            'products' => 'sometimes|array',
            'products.*.product_id' => 'integer|exists:products,product_id',
            'products.*.weight' => 'numeric',
            'products.*.factor_ids' => 'array',
        ]);
    }

    private function handleProductRelatedDish($validatedData, $request)
    {
        $productHandlingResult = $this->processProducts($request->input('products'));
        if ($productHandlingResult['error']) {
            return response()->json(['error' => $productHandlingResult['message']], 400);
        }

        $validatedData = array_merge($validatedData, $productHandlingResult['totals']);

        $dish = Dish::create($validatedData);
        $dish->products()->attach($productHandlingResult['productsData']);
        $dish->nutrients()->attach($this->prepareNutrientsData($request));

        return response()->json($dish, 201);
    }

    private function handleSimpleDish($validatedData, $request)
    {
        $dish = Dish::create($validatedData);
        $dish->nutrients()->attach($this->prepareNutrientsData($request));

        return response()->json($dish, 201);
    }

    private function processProducts($products)
    {
        $totalPrice = 0; 
        $totalWeight = 0;
        $totalKilocalories = 0;
        $totalKilocaloriesWithFiber = 0;
        $nutrientsTotals = []; 

        $totalProtein = 0;
        $totalFat = 0;
        $totalCarbohydrate = 0;
        $productsData = [];

        $validatedData['has_relation_with_products'] = true;

        $products = $this->productFetchService->completeProductRequest($products);

        if (is_null($products) || !is_array($products)) {
            return response()->json(['error' => 'Invalid products data'], 400);
        }

        $customWeightAdjustedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);

        $weightLossAfterColdProcessing = $this->nutrientCalculationService->calculateWeightForColdProcessing($customWeightAdjustedProducts);

        $customWeightAdjustedAfterColdProcessing = $this->weightCalculationService->calculateNutrientsForCustomWeightAfterColdProcessing($weightLossAfterColdProcessing);

        $weightLossAfterThermalProcessing = $this->nutrientCalculationService->calculateWeightForThermalProcessing($customWeightAdjustedAfterColdProcessing);

        $nutrientLossAfterThermalProcessing = $this->nutrientCalculationService->calculateNutrients($weightLossAfterThermalProcessing);
        
        foreach ($nutrientLossAfterThermalProcessing as $product) {
            $totalPrice += $product['price'];
            $totalWeight += $product['weight'];
            $totalKilocalories += $product['kilocalories'];
        
            foreach ($product['nutrients'] as $nutrient) {
                if (!in_array($nutrient['nutrient_id'], [2, 3, 4])) {
                    if (!isset($nutrientsTotals[$nutrient['nutrient_id']])) {
                        $nutrientsTotals[$nutrient['nutrient_id']] = 0;
                    }
                    $nutrientsTotals[$nutrient["nutrient_id"]] += $nutrient["pivot"]['weight'];
                } else {
                    switch ($nutrient['nutrient_id']) {
                        case 2: 
                            $totalProtein += $nutrient["pivot"]['weight'];
                            break;
                        case 3: 
                            $totalFat += $nutrient["pivot"]['weight'];
                            break;
                        case 4: 
                            $totalCarbohydrate += $nutrient["pivot"]['weight'];
                            break;
                    }
                }
            }
                
        
            $bruttoWeight = $product['brutto_weight'] ?? $product['weight']; 

            $productsData[] = [
                'product_id' => $product['product_id'],
                'weight' => $bruttoWeight,
                'name' => $product['name'],
                'kilocalories' => $product['kilocalories'],
                'price' => $product['price'],

                'factor_ids' => json_encode($product['factor_ids']),
                'nutrients' => json_encode($product['nutrients'])
            ];
        }

        return [
            'error' => false,
            'totals' => [
                'price' => $totalPrice,
                'kilocalories' => $totalKilocalories,
                'weight' => $totalWeight,
                'protein' => $totalProtein,
                'fat' => $totalFat,
                'carbohydrate' => $totalCarbohydrate,
            ],
            'productsData' => $productsData
        ];
    }

    private function prepareNutrientsData($request)
    {
        $nutrientsData = [];
        if ($request->has('nutrients')) {
            foreach ($request->input('nutrients') as $nutrient) {
                if (!in_array($nutrient['nutrient_id'], self::NUTRIENT_IDS)) {
                    $nutrientsData[$nutrient['nutrient_id']] = ['weight' => $nutrient['weight']];
                }
            }
        }
        return $nutrientsData;
    }
   

    /**
     * @OA\Get(
     *     path="/dishes/{id}",
     *     operationId="showDish",
     *     tags={"Dishes"},
     *     summary="Get a specific dish",
     *     description="Retrieves a specific dish by its ID, including related data.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Dish ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Dish")
     *     ),
     *     @OA\Response(response=404, description="Dish not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(int $id)
    {
        $dish = Dish::findOrFail($id);
    
        $dish->load([
            'nutrients' => function ($query) {
                $query->whereIn('name', config('nutrients.nutrient_names'))
                ->withPivot('weight');
            },
            'menuMealTimes.menu' => function ($query) {
                $query->select('menu_id', 'name');
            },
            'menuMealTimes.mealTime',

            'products' => function ($query) {
                $query->withPivot('weight', 'price', 'kilocalories', 'nutrients', 'factor_ids');
            },
        ]);
    
        $menus = $dish->menuMealTimes->map(function ($menuMealTime) {
            return [
                'menu_id' => $menuMealTime->menu->menu_id,
                'name' => $menuMealTime->menu->name,
            ];
        })->unique('menu_id')->values(); 
    
        $dish->menus = $menus;
    
        unset($dish->menuMealTimes);

        $dish->products->each(function ($product) {
            $product->pivot->factor_ids = json_decode($product->pivot->factor_ids, true);
        });
    
        return response()->json($dish);
    }
    

    /**
     * @OA\Delete(
     *     path="/dishes/{id}",
     *     operationId="deleteDish",
     *     tags={"Dishes"},
     *     summary="Delete a dish",
     *     description="Deletes a specific dish by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Dish ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Dish deleted successfully"
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=404, description="Dish not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy(Dish $dish)
    {
        DB::beginTransaction();
        try {
            $dish->products()->detach(); 
            $dish->nutrients()->detach();
            $dish->delete(); 
            DB::commit(); 
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\NutrientCalculationService;
use App\Services\WeightCalculationService;
use App\Services\ProductFetchService;
use App\Services\DishCreationService;

use App\Models\Dish;
use App\Models\DishCategory;

use Illuminate\Support\Facades\Validator;

class DishController extends Controller
{
    protected $nutrientCalculationService;
    protected $weightCalculationService;
    protected $productFetchService;

    protected $dishCreationService;

    public function __construct(NutrientCalculationService $nutrientCalculationService, WeightCalculationService $weightCalculationService, ProductFetchService $productFetchService, DishCreationService $dishCreationService)
    {
        $this->nutrientCalculationService = $nutrientCalculationService;
        $this->weightCalculationService = $weightCalculationService;
        $this->productFetchService = $productFetchService;

        $this->dishCreationService = $dishCreationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        // $dishes = Dish::with('products', 'nutrients')->get();
        // return response()->json($dishes);

        // Validate the request
        $request->validate([
            'search' => 'string|nullable',
            'dish_id' => 'integer|nullable',
            'dish_category_id' => 'integer|nullable',
            'per_page' => 'integer|nullable',
            'page' => 'integer|nullable' // Add validation for 'page'
        ]);

        // Check for specific product ID search
        if ($request->has('dish_id')) {
            // call the show function in this class
            return $this->show($request->input('dish_id'));
        }

        // Continue with the regular search and filtering
        $query = Dish::with(['dishCategory' => function($query) {
                $query->select('dish_category_id', 'name');
            }])
            ->select(['dish_id', 'bls_code', 'name', 'description', 'health_factor', 'dish_category_id', 'has_relation_with_products']);


        // Handle the general search parameter
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

        // Filter by product_category_id if provided
        if ($request->has('dish_category_id')) {
            $query->whereHas('dishCategory', function ($q) use ($request) {
                $q->where('dish_category_id', $request->input('dish_category_id'));
            });
        }

        // Determine the number of products per page
        $perPage = $request->input('per_page', 10); // Default to 10 if not provided
        $currentPage = $request->input('page', 1); // Default to 1 if not provided

        // Get the results with pagination
        $dishes = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // Optional: Customize the response format
        return response()->json([
            'current_page' => $dishes->currentPage(),
            'items_per_page' => $dishes->perPage(),
            'total_items' => $dishes->total(),
            'total_pages' => $dishes->lastPage(),
            'data' => $dishes->items()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bls_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recipe_description' => 'nullable|string',
            'dish_category_id' => 'required|integer|exists:dish_categories,dish_category_id',
            'dish_category_code' => 'nullable|string|max:255',
            // 'kilocalories' => 'required|numeric',
            // 'kilocalories_with_fiber' => 'nullable|numeric',
            // 'price' => 'required|numeric',
            'image_url' => 'nullable|url',
            'health_factor' => 'nullable|numeric',

            'nutrients' => 'sometimes|array',

            // Validate the optional products array
            'products' => 'sometimes|array',
            'products.*.product_id' => 'required_with:products|integer|exists:products,product_id',
            'products.*.weight' => 'required_with:products|numeric',
            'products.*.factor_ids' => 'sometimes:products|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $validatedData = $validator->validated();

        $totalPrice = 0; // Initialize total price
        $totalWeight = 0;
        $totalKilocalories = 0;
        $totalKilocaloriesWithFiber = 0;
        $nutrientsTotals = []; // Initialize nutrients totals

        // macros
        $totalProtein = 0;
        $totalFat = 0;
        $totalCarbohydrate = 0;


        if ($request->has('products')) {

            $validatedData['has_relation_with_products'] = true;

            $requestData = $request->input('products'); 
            $products = $this->productFetchService->completeProductRequest($requestData);

            if (is_null($products) || !is_array($products)) {
                return response()->json(['error' => 'Invalid products data'], 400);
            }

            // Process products data
            $processedProducts = $this->weightCalculationService->calculateNutrientsForCustomWeight($products);
            $productsWithUpdatedWeights = $this->nutrientCalculationService->calculateWeight($processedProducts);
            $productsWithUpdatedNutrients = $this->nutrientCalculationService->calculateNutrients($productsWithUpdatedWeights); 

            $productsData = [];
            foreach ($productsWithUpdatedNutrients as $product) {
                $totalPrice += $product['price'];
                $totalWeight += $product['weight'];
                $totalKilocalories += $product['kilocalories'];
                // \Log::info('Product name', ['product' => $product['name']]);
                // $totalKilocaloriesWithFiber += $product['kilocalories_with_fiber'];
            
                foreach ($product['nutrients'] as $nutrient) {
                    // Exclude specific macronutrients by their IDs
                    if (!in_array($nutrient['nutrient_id'], [2, 3, 4])) {
                        // Aggregate totals for other nutrients
                        if (!isset($nutrientsTotals[$nutrient['nutrient_id']])) {
                            $nutrientsTotals[$nutrient['nutrient_id']] = 0;
                        }
                        $nutrientsTotals[$nutrient["nutrient_id"]] += $nutrient["pivot"]['weight'];
                    } else {
                        // Sum up macronutrients separately
                        switch ($nutrient['nutrient_id']) {
                            case 2: // Assuming ID 2 is for protein
                                $totalProtein += $nutrient["pivot"]['weight'];
                                break;
                            case 3: // Assuming ID 3 is for fat
                                $totalFat += $nutrient["pivot"]['weight'];
                                break;
                            case 4: // Assuming ID 4 is for carbohydrate
                                $totalCarbohydrate += $nutrient["pivot"]['weight'];
                                break;
                        }
                    }
                }
                
            
                $bruttoWeight = $product['brutto_weight'] ?? $product['weight']; // Fallback to 'weight' if 'brutto_weight' is not set
 
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
            

            $validatedData['price'] = $totalPrice;
            $validatedData['weight'] = $totalWeight;
            $validatedData['kilocalories'] = $totalKilocalories;
            // $validatedData['kilocalories_with_fiber'] = $totalKilocaloriesWithFiber;

            // macros
            $validatedData['protein'] = $totalProtein;
            $validatedData['fat'] = $totalFat;
            $validatedData['carbohydrate'] = $totalCarbohydrate;



            $dish = Dish::create($validatedData);
            
            $dish->products()->attach($productsData);
        } else {
            $validatedData['price'] = $request->input('price');
            $validatedData['weight'] = $request->input('weight', 0);
            $validatedData['kilocalories'] = $request->input('kilocalories', 0);
            // $validatedData['kilocalories_with_fiber'] = $request->input('kilocalories_with_fiber');
        
            // macros
            $validatedData['protein'] = $request->input('protein', 0);
            $validatedData['fat'] = $request->input('fat', 0);
            $validatedData['carbohydrate'] = $request->input('carbohydrate', 0);
        
            // Create the dish with validated data
            $dish = Dish::create($validatedData);

            $excludedNutrientIds = [2, 3, 4]; // IDs for protein, fat, carbohydrate
        
            // Check if 'nutrients' is provided in the request
            if ($request->has('nutrients')) {
                $nutrientsData = [];
                foreach ($request->input('nutrients') as $nutrient) {
                    $nutrientId = $nutrient['nutrient_id'];
                    $weight = $nutrient['weight'];
                    
                    // Assuming there's a need to validate or sanitize $nutrientId and $weight
                    // This is just an example. Ensure to validate these fields as per your application's needs.
                    if (is_numeric($nutrientId) && is_numeric($weight)) {
                        $nutrientsData[$nutrientId] = ['weight' => $weight];
                    }
                }
                // Attach nutrients to the dish
                // Ensure that your Dish model has a properly defined relationship to nutrients
                $dish->nutrients()->attach($nutrientsData);
            }
        
            // // Optionally, return a response or perform additional operations
        }
        return response()->json($dish, 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $dish = Dish::findOrFail($id);
    
        // Eager load nutrients and MenuMealTimes with restricted Menu fields
        $dish->load([
            'nutrients' => function ($query) {
                $query->whereIn('name', config('nutrients.nutrient_names'))
                ->withPivot('weight');
            },
            'menuMealTimes.menu' => function ($query) {
                // Only select menu_id and name from the Menu
                $query->select('menu_id', 'name');
            },
            'menuMealTimes.mealTime',

            'dishesProducts' => function ($query) {
                $query->select('product_id', 'name', 'weight', 'factor_ids');
            },
        ]);
    
        // Transform the data to include only the menu id and name
        $menus = $dish->menuMealTimes->map(function ($menuMealTime) {
            return [
                'menu_id' => $menuMealTime->menu->menu_id,
                'name' => $menuMealTime->menu->name,
            ];
        })->unique('menu_id')->values(); // Ensures each menu is listed only once and reindexes the array
    
        // Add menus information directly to the dish object
        $dish->menus = $menus;
    
        // Remove menuMealTimes relationship to clean up the response
        unset($dish->menuMealTimes);
    
        return response()->json($dish);
    }
    





    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dish $dish)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dish $dish)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dish $dish)
    {
        DB::beginTransaction();
        try {
            $dish->products()->detach(); // Detach the related products assuming a many-to-many relationship
            $dish->delete(); // Delete the dish
            DB::commit(); // Commit the transaction
            return response()->json(null, 204); // Return a 204 No Content response
        } catch (\Exception $e) {
            DB::rollBack(); // Roll back the transaction in case of an error
            return response()->json(['error' => $e->getMessage()], 500); // Return a 500 Internal Server Error response
        }
    }

}

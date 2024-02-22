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
        $validatedData = $request->validate([
            'bls_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'recipe_description' => 'nullable|string',
            'dish_category_id' => 'required|integer|exists:dish_categories,dish_category_id',
            'dish_category_code' => 'nullable|string|max:255',
            'kilocalories' => 'required|numeric',
            'kilocalories_with_fiber' => 'nullable|numeric',
            'image_url' => 'nullable|url',
            'health_factor' => 'required|numeric',
            'price' => 'required|numeric',

            'nutrients' => 'nullable|array',

            // Validate the optional products array
            'products' => 'nullable|array',
            'products.*.product_id' => 'required_with:products|integer|exists:products,product_id',
            // 'products.*.kilocalories' => 'required_with:products|numeric',
            'products.*.weight' => 'required_with:products|numeric',
            // 'products.*.price' => 'required_with:products|numeric', // Added price validation
            // 'products.*.kilocalories_with_fiber' => 'nullable|numeric', // Added kilocalories_with_fiber validation
            // 'products.*.nutrients' => 'required_with:products|array', // Added nutrients validation
            

        ]);

        $totalPrice = 0; // Initialize total price
        $nutrientsTotals = []; // Initialize nutrients totals

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
                // Calculate total price
                $totalPrice += $product['price'];

                // Assuming 'nutrients' is an array of nutrient_id => value
                foreach ($product['nutrients'] as $nutrient) {
                    if (!isset($nutrientsTotals[$nutrient['nutrient_id']])) {
                        $nutrientsTotals[$nutrient['nutrient_id']] = 0;
                    }
                    $nutrientsTotals[$nutrient["nutrient_id"]] += $nutrient["weight"];
                }

                $productsData[$product['product_id']] = [
                    'weight' => $product['weight'],
                    // Other product data...
                ];
            }

            // Attach product IDs with additional pivot data to the dish
            $dish = Dish::create($validatedData + ['total_price' => $totalPrice]); // Save dish with total price
            $dish->products()->attach($productsData);
        } else {
            $dish = Dish::create($validatedData);
        }

        // Attach nutrients' totals to the dish
        if (!empty($nutrientsTotals)) {
            $nutrientsData = [];
            foreach ($nutrientsTotals as $nutrientId => $total) {
                $nutrientsData[$nutrientId] = ['total' => $total];
            }
            $dish->nutrients()->attach($nutrientsData);
        } elseif ($request->has('nutrients')) {
            // Handle case where nutrients are directly provided in the request
            // Similar logic to attach directly provided nutrients
        }

        return response()->json($dish, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        // Fetch the dish by its ID
        $dish = Dish::findOrFail($id);

        // Attempt to load products to check if the dish has any related products
        $productsCount = $dish->products()->count();

        if ($dish->products->isNotEmpty()) {
            $dish->products->each(function ($product) {
                // Assuming the pivot data is loaded and includes 'nutrients'
                // Check if the pivot data exists and unset 'nutrients'
                if (isset($product->pivot->nutrients)) {
                    unset($product->pivot->nutrients);
                }
            });
        } else {
            // If no related products, load the nutrients directly
            $dish->load([
                'nutrients' => function($query) {
                    $query->withPivot('weight');
                }
            ]);
        }

        // Return the dish data as a JSON response
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

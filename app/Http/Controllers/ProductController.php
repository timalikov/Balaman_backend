<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Http\Resources\DishResource;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validate the request
        $request->validate([
            'search' => 'string|nullable',
            'product_id' => 'integer|nullable',
            'product_category_id' => 'integer|nullable',
            'per_page' => 'integer|nullable',
            'page' => 'integer|nullable'
        ]);

        // Check for specific product ID search
        if ($request->has('product_id')) {
            return $this->show($request->input('product_id'));
        }

        $query = Product::with([
                'productCategory' => function($query) {
                    $query->select('product_category_id', 'name');
                },
                'factors' => function($query) { // Include factors relationship
                    $query->select('factors.factor_id'); // Select only factor_id
                }
            ])
            ->select(['product_id', 'bls_code', 'name', 'description', 'product_category_id']);

        // Handle the general search parameter
        if ($request->has('search')) {
            $searchTerm = strtolower($request->input('search'));
            $searchTerm = str_replace(' ', '', $searchTerm); // Remove spaces from the search term

            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('REPLACE(LOWER(name), \' \', \'\') LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('REPLACE(LOWER(description), \' \', \'\') LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereHas('productCategory', function ($q) use ($searchTerm) {
                    $q->whereRaw('REPLACE(LOWER(name), \' \', \'\') LIKE ?', ['%' . $searchTerm . '%']);
                });
            });
        }

        // Filter by product_category_id if provided
        if ($request->has('product_category_id')) {
            $query->whereHas('productCategory', function ($q) use ($request) {
                $q->where('product_category_id', $request->input('product_category_id'));
            });
        }


        // Finalize the query with pagination
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);
        $products = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // Prepare the data to include factors and factor_ids
        $modifiedData = $products->getCollection()->map(function ($product) {
            // Extract only factor_ids from the factors relationship
            $factorIds = $product->factors->pluck('factor_id');
            // Assign the extracted factor_ids to the product
            $product->factor_ids = $factorIds;
            // Remove the factors attribute from the product to not include it in the response
            unset($product->factors); // This line ensures the factors array is removed from the return data
            return $product;
        });
        

        return response()->json([
            'current_page' => $products->currentPage(),
            'items_per_page' => $products->perPage(),
            'total_items' => $products->total(),
            'total_pages' => $products->lastPage(),
            'data' => $modifiedData
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
        // Validate the incoming request data
        $validatedData = $request->validate([
            'bls_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_category_id' => 'required|integer',
            'product_category_code' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'protein' => 'required|numeric',
            'fat' => 'required|numeric',
            'carbohydrate' => 'required|numeric',
            'fiber' => 'nullable|numeric',
            'total_sugar' => 'nullable|numeric',
            'saturated_fat' => 'nullable|numeric',
            'kilocaries' => 'required|numeric',
            'kilocaries_with_fiber' => 'nullable|numeric',
            'image_url' => 'nullable|url',
            'is_seasonal' => 'nullable|boolean',
        ]);

        // Create the product
        $product = Product::create($validatedData);

        // Return a response, e.g., the created product or a success message
        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        // Fetch the product by its ID along with related data
        $product = Product::with([
            // Include the micronutrients ('nutrients') associated with the product
            'nutrients' => function ($query) {
                // Ensure to fetch the weight from the pivot table for each nutrient
                $nutrientNames = [
                    'water', 'protein', 'fat', 'carbohydrate', 'vitaminA', 'vitaminD', 'vitaminE', 
                    'vitaminK', 'vitaminB1', 'vitaminB2', 'vitaminB3', 'vitaminB5', 'vitaminB6', 
                    'vitaminB7', 'vitaminB9', 'vitaminB12', 'vitaminC', 'potassium', 'calcium', 
                    'magnesium', 'phosphorus', 'iron', 'zinc', 'copper', 'iodine', 'sodium'
                ];
                $query->whereIn('name', $nutrientNames)
                      ->withPivot('weight');
            }, 
    
            // Include the product's category
            'productCategory' => function ($query) {
                // Select only the necessary fields from the productCategory table
                // Adjust the field names if they are different in your database
                $query->select('product_category_id', 'name'); // Ensure correct field names are used here
            },
    
            // Include dishes where the product is used
            'dishes' => function ($query) {
                $query->select('dishes.dish_id', 'dishes.name');
            }

        ])
        // Filter the product by its unique ID
        ->where('product_id', $id) // Make sure the column name matches your schema
        // Fetch the first product that matches the criteria or fail
        ->firstOrFail();
    
        $product->dishes->transform(function ($dish) {
            return collect($dish->toArray())->except(['pivot']);
        });

        // Return the product data as a JSON response
        return response()->json($product);
    }
    
    


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        //
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
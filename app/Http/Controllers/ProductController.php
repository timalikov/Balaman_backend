<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;

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
            'page' => 'integer|nullable' // Add validation for 'page'
        ]);

        // Check for specific product ID search
        if ($request->has('product_id')) {
            // call the show function in this class
            return $this->show($request->input('product_id'));
        }

        // Continue with the regular search and filtering
        $query = Product::with(['productCategory' => function($query) {
                $query->select('product_category_id', 'name');
            }])
            ->select(["product_id", 'name', 'description', 'product_category_id']);


        // Handle the general search parameter
        if ($request->has('search')) {
            $searchTerm = $request->input('search');

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('productCategory', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            });
        }

        // Filter by product_category_id if provided
        if ($request->has('product_category_id')) {
            $query->whereHas('productCategory', function ($q) use ($request) {
                $q->where('product_category_id', $request->input('product_category_id'));
            });
        }

        // // Determine the number of products per page
        // $perPage = $request->input('per_page', 10); // Default to 10 if not provided

        // // Get the results with pagination
        // $products = $query->paginate($perPage);

        // return response()->json($products);

        // Determine the number of products per page
        $perPage = $request->input('per_page', 10); // Default to 10 if not provided
        $currentPage = $request->input('page', 1); // Default to 1 if not provided

        // Get the results with pagination
        $products = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // Optional: Customize the response format
        return response()->json([
            'current_page' => $products->currentPage(),
            'items_per_page' => $products->perPage(),
            'total_items' => $products->total(),
            'total_pages' => $products->lastPage(),
            'data' => $products->items()
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
    public function show(string $id)
    {
        // Fetch the product by its ID along with related data
        $product = Product::with([
            // Include the micronutrients ('nutrients') associated with the product
            'nutrients' => function ($query) {
                // Ensure to fetch the weight from the pivot table for each nutrient
                $query->withPivot('weight');
            }, 
    
            // Include the product's category
            'productCategory' => function ($query) {
                // Select only the necessary fields from the productCategory table
                // Adjust the field names if they are different in your database
                $query->select('product_category_id', 'name');
            }
        ])
        // Filter the product by its unique ID
        ->where('product_id', $id)
        // Fetch the first product that matches the criteria or fail
        ->firstOrFail();
    
        // Return the product data as a JSON response
        return response()->json($product);
    }
    


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Validate the request
        $request->validate([
            'product_name_filter' => 'string|nullable',
            'category_id_filter' => 'integer|nullable',
        ]);

        $query = Product::query();

        $query = Product::with('productCategory:name') 
        ->select(["product_id", 'name', 'description', 'product_category_id', 'price', 'protein', 'fat', 'carbohydrate', 'fiber', 'kilocaries']);


        // Filter by name
        if ($request->has('product_name_filter')) {
            $query->where('name', 'like', '%' . $request->input('product_name_filter') . '%');
        }

        // Filter by category
        if ($request->has('category_id_filter')) {
            $query->where('product_category_id', $request->input('category_id_filter'));
        }

        $products = $query->get();

        return response()->json($products);
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
            'protein' => 'nullable|numeric',
            'fat' => 'nullable|numeric',
            'carbohydrate' => 'nullable|numeric',
            'fiber' => 'nullable|numeric',
            'total_sugar' => 'nullable|numeric',
            'saturated_fat' => 'nullable|numeric',
            'kilocaries' => 'nullable|numeric',
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
        //
        $product = Product::with('micros')->findOrFail($id);

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
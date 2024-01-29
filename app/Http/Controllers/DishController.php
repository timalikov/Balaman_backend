<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $dishes = Dish::with('products')->get();
        return response()->json($dishes);
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
            'has_relation_with_products' => 'required|boolean',
            'health_factor' => 'required|numeric',

             // Validate the optional products array
            'products' => 'sometimes|array',
            'products.*.id' => 'required_with:products|integer|exists:products,id',
            'products.*.kilocalories' => 'required_with:products|numeric',
            'products.*.weight' => 'required_with:products|numeric',

        ]);

        // Start transaction
        DB::beginTransaction();

        try {
            $dish = Dish::create($validatedData);

            // Check if products data is present
            if (!empty($validatedData['products'])) {
                foreach ($validatedData['products'] as $product) {
                    // Here you need to insert data into your pivot table
                    // Assuming you have a many-to-many relationship set up with Dish and Product models
                    $dish->products()->attach($product['id'], [
                        'kilocalories' => $product['kilocalories'],
                        'weight' => $product['weight'],
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();

            return response()->json($dish, 201); 
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollback();

            // Handle the error, maybe log it and return a custom error message
            return response()->json(['error' => 'An error occurred while saving the dish.'], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Dish $dish)
    {
        //≠
        $dish = Dish::with('products')->findOrFail($id);
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
        //
        $dish = Dish::findOrFail($id);
        DB::beginTransaction();
        try {
            $dish->products()->detach();
            $dish->delete();
            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

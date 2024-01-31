<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class DishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $dishes = Dish::with('products', 'nutrients')->get();
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
            'price' => 'required|numeric',

            'nutrients' => 'nullable|array',

            // Validate the optional products array
            'products' => 'nullable|array',
            'products.*.product_id' => 'required_with:products|integer|exists:products,product_id',
            'products.*.kilocalories' => 'required_with:products|numeric',
            'products.*.weight' => 'required_with:products|numeric',
            'products.*.price' => 'required_with:products|numeric', // Added price validation
            'products.*.kilocalories_with_fiber' => 'nullable|numeric', // Added kilocalories_with_fiber validation
            'products.*.nutrients' => 'required_with:products|array', // Added nutrients validation
            

        ]);

        if ($request->has('products')) {
            $validatedData['has_relation_with_products'] = true;
        }else{
            $validatedData['has_relation_with_products'] = false;
        }
        // Create the dish
        $dish = Dish::create($validatedData);

        // Check if the request has 'products' array
        if ($request->has('products')) {
            $productsData = [];
            foreach ($request->input('products') as $product) {
                // Assuming each product in the array has 'product_id' and 'product_weight'
                $productsData[$product['product_id']] = [
                    'weight' => $product['weight'],
                    'kilocalories' => $product['kilocalories'],
                    'price' => $product['price'], // Added price
                    'kilocalories_with_fiber' => $product['kilocalories_with_fiber'], // Added kilocalories_with_fiber
                    'nutrients' => json_encode($product['nutrients']) // Encoding the nutrients array as JSON
                ];
            }

            // Attach product IDs with additional pivot data to the dish
            $dish->products()->attach($productsData);
        } elseif ($request->has('nutrients')) {
            $nutrientsData = [];
            foreach($request->input('nutrients') as $nutrient){
                $nutrientsData[$nutrient['nutrient_id']] = [
                    'weight' => $nutrient['pivot']['weight'],
                ];
            }
            $dish->nutrients()->attach($nutrientsData);
        }


        return response()->json($dish, 201);

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

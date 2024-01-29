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
        $data = $request->validate([
            'bls_code' => 'required|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'recipe_description' => 'nullable|string',

            'dish_category_id' => 'exists:dish_categories,category_id',
            'dish_category_code' => 'nullable|string',

            'image_url' => 'nullable|string',
            'has_relation_with_products' => 'nullable|boolean',
            'health_factor' => 'nullable|integer',

            'kilocalories' => 'required|numeric',
            'kilocalories_with_fiber' => 'nullable|numeric',

            'products' => 'sometimes|array',
            'products.*.product_id' => 'required_with:products|exists:products,product_id',
            'products.*.nutrients' => 'required_with:products.*.product_id|array', 

        ]);

        DB::beginTransaction();
        try {
            $dish = Dish::create([
                'name' => $data['name'],
                'description' => $data['description']
            ]);

            if (!empty($data['products'])) {
                foreach ($data['products'] as $productData) {
                    $dish->products()->attach($productData['product_id'], [
                        'nutrients' => json_encode($productData['nutrients'] ?? [])
                    ]);
                }
            }

            DB::commit();
            return response()->json($dish->load('products'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
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

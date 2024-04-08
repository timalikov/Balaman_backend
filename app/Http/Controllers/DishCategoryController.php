<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DishCategory;
use Illuminate\Support\Facades\Validator;

class DishCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all dish categories
        $dishCategories = DishCategory::select('dish_category_id', 'name')->get();

        return response()->json($dishCategories);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:dish_categories,name', // Ensuring name is required, a string, not too long, and unique
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Return validation errors with 422 Unprocessable Entity status code
        }

        // Create new DishCategory instance and save it to the database
        $dishCategory = new DishCategory();
        $dishCategory->name = $request->name;
        $dishCategory->save();

        
        // Return the newly created dish category with a 201 Created status code
        return response()->json($dishCategory, 201);

    }

    /** 
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        // Find the dish category by its custom primary key
        $dishCategory = DishCategory::where('dish_category_id', $id)->first();

        // Check if the dish category was found
        if (!$dishCategory) {
            return response()->json(['message' => 'Dish category not found.'], 404); // Dish category not found
        }

        // Attempt to delete the dish category
        try {
            $dishCategory->delete();
            return response()->json(['message' => 'Dish category deleted successfully.'], 200); // Successfully deleted
        } catch (\Exception $e) {
            // If there's an exception (e.g., foreign key constraint fails), return an error message
            return response()->json(['message' => 'Failed to delete the dish category. It may be in use.'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DishCategory;
use Illuminate\Support\Facades\Validator;

class DishCategoryController extends Controller
{
    public function index()
    {
        $dishCategories = DishCategory::select('dish_category_id', 'name')->get();

        return response()->json($dishCategories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:dish_categories,name', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); 
        }

        $dishCategory = new DishCategory();
        $dishCategory->name = $request->name;
        $dishCategory->save();
        
        return response()->json($dishCategory, 201);

    }

    public function destroy(string $id)
    {
        $dishCategory = DishCategory::where('dish_category_id', $id)->first();

        if (!$dishCategory) {
            return response()->json(['message' => 'Dish category not found.'], 404);
        }

        try {
            $dishCategory->delete();
            return response()->json(['message' => 'Dish category deleted successfully.'], 200); 
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the dish category. It may be in use.'], 500);
        }
    }
}

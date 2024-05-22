<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;


class ProductCategoryController extends Controller
{
    public function index()
    {
        $productCategories = ProductCategory::select('product_category_id', 'name')->get();

        return response()->json($productCategories);

    }
}

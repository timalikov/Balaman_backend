<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Http\Resources\DishResource;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable',
            'product_id' => 'integer|nullable',
            'product_category_id' => 'integer|nullable',
            'per_page' => 'integer|nullable',
            'page' => 'integer|nullable'
        ]);

        if ($request->has('product_id')) {
            return $this->show($request->input('product_id'));
        }

        $query = Product::with([
                'productCategory' => function($query) {
                    $query->select('product_category_id', 'name');
                },
                'factors' => function($query) {
                    $query->select('factors.factor_id'); 
                }
            ])
            ->select(['product_id', 'bls_code', 'name', 'description', 'product_category_id']);

        if ($request->has('search')) {
            $searchTerm = strtolower($request->input('search'));
            $searchTerm = str_replace(' ', '', $searchTerm); 

            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('REPLACE(LOWER(name), \' \', \'\') LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereRaw('REPLACE(LOWER(description), \' \', \'\') LIKE ?', ['%' . $searchTerm . '%'])
                ->orWhereHas('productCategory', function ($q) use ($searchTerm) {
                    $q->whereRaw('REPLACE(LOWER(name), \' \', \'\') LIKE ?', ['%' . $searchTerm . '%']);
                });
            });
        }

        if ($request->has('product_category_id')) {
            $query->whereHas('productCategory', function ($q) use ($request) {
                $q->where('product_category_id', $request->input('product_category_id'));
            });
        }

        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);
        $products = $query->paginate($perPage, ['*'], 'page', $currentPage);

        $modifiedData = $products->getCollection()->map(function ($product) {
            $factorIds = $product->factors->pluck('factor_id');
            $product->factor_ids = $factorIds;
            unset($product->factors); 
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

    public function store(Request $request)
    {
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
            'kilocalories' => 'required|numeric',
            'image_url' => 'nullable|url',
        ]);

        $product = Product::create($validatedData);

        return response()->json($product, 201);
    }

    public function show(int $id)
    {
        $product = Product::with([
            'nutrients' => function ($query) {
                $query->whereIn('name', config('nutrients.nutrient_names'))
                      ->withPivot('weight');
            }, 
    
            'productCategory' => function ($query) {
                $query->select('product_category_id', 'name'); 
            },
    
            'dishes' => function ($query) {
                $query->select('dishes.dish_id', 'dishes.name');
            }

        ])
        ->where('product_id', $id)
        ->firstOrFail();
    
        $product->dishes->transform(function ($dish) {
            return collect($dish->toArray())->except(['pivot']);
        });

        return response()->json($product);
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
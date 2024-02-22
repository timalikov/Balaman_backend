<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $primaryKey = 'dish_id';

    protected $fillable = [
        'bls_code', 'name', 'description', 'recipe_description', 
        'dish_category_id', 'dish_category_code', 'image_url', 
        'has_relation_with_products', 'health_factor', 'kilocalories', 
        'kilocalories_with_fiber'
    ];

    public function dishCategory()
    {
        return $this->belongsTo(DishCategory::class, 'dish_category_id', 'dish_category_id');
    }

    public function nutrients()
    {
        return $this->belongsToMany(Nutrient::class, 'dishes_nutrients', 'dish_id', 'nutrient_id')
                    ->withPivot(['weight']);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'dishes_products', 'dish_id', 'product_id')
                    ->withPivot(['weight', 'price', 'kilocalories', 'kilocalories_with_fiber', 'nutrients']);
    }



}

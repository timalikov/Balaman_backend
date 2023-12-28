<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $primaryKey = 'dish_id';

    protected $fillable = [
        'bls_code',
        'name',
        'description',
        'recipe_description',
        'dish_category_id',
        'dish_category_code',
        'image_url',
        'has_relation_with_products',
        'health_factor',
        'protein',
        'fat',
        'carbohydrate',
        'fiber',
        'total_sugar',
        'saturated_fat',
        'kilocaries',
        'kilocaries_with_fiber',

    ];

    public function dishCategory()
    {
        return $this->belongsTo(DishCategory::class, 'dish_category_id', 'category_id');
    }

    public function micros()
    {
        return $this->belongsToMany(Micro::class, 'dishes_micros', 'dish_id', 'micro_id');
    }



}

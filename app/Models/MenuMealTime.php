<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuMealTime extends Model
{
    use HasFactory;

    protected $table = 'menu_meal_times'; 

    protected $primaryKey = 'menu_meal_time_id'; 

    protected $fillable = [
        'menu_id',
        'meal_time_name',
        'meal_time_number',
        'day_of_week',
        'week',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }

    public function mealDishes()
    {
        return $this->belongsToMany(Dish::class, 'meal_dishes', 'menu_meal_time_id', 'dish_id')
                    ->wherePivot('product_id', null)  
                    ->withPivot('weight');
    }

    public function mealProducts()
    {
        return $this->belongsToMany(Product::class, 'meal_dishes', 'menu_meal_time_id', 'product_id')
                    ->wherePivot('dish_id', null) 
                    ->withPivot('weight');
    }

    public function productFactors()
    {
        return $this->belongsToMany(Product::class, 'products_for_menu', 'menu_meal_time_id', 'product_id')
                    ->withPivot('factor_ids') 
                    ->as('productDetails');
    }




}

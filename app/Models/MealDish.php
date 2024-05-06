<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealDish extends Model
{
    protected $primaryKey = 'meal_dish_id';

    protected $fillable = ['menu_meal_time_id', 'dish_id', 'product_id', 'weight'];

    public function menuMealTime()
    {
        return $this->belongsTo(MenuMealTime::class, 'menu_meal_time_id');
    }

}

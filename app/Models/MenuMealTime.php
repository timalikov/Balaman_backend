<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuMealTime extends Model
{
    use HasFactory;

    protected $primaryKey = 'menu_meal_time_id'; // Set primary key
    protected $table = 'menu_meal_times'; // Explicitly define table name

    // Assuming both menu_id and meal_time_id can be mass assigned along with day_of_week and week
    protected $fillable = [
        'menu_id',
        'meal_time_id',
        'day_of_week',
        'week',
    ];

    /**
     * Menu relationship.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'menu_id');
    }

    /**
     * MealTime relationship.
     */
    public function mealTime()
    {
        return $this->belongsTo(MealTime::class, 'meal_time_id', 'meal_time_id');
    }


    public function mealDishes()
    {
        return $this->hasMany(MealDish::class, 'menu_meal_time_id');
    }


}

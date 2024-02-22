<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealTime extends Model
{
    use HasFactory;

    protected $primaryKey = 'meal_time_id';

    protected $fillable = [
        'name'
    ];
}

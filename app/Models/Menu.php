<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $primaryKey = 'menu_id';

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'status',
        'season',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function menuMealTimes()
    {
        return $this->hasMany(MenuMealTime::class, 'menu_id', 'menu_id');
    }


}

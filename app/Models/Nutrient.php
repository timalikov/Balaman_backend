<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class nutrient extends Model
{
    use HasFactory;

    protected $primaryKey = 'nutrient_id';
     
    protected $fillable = [
        'name',
        'code',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'nutrients_products', 'nutrient_id', 'product_id');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dishes_nutrients', 'nutrient_id', 'dish_id');
    }

    
}

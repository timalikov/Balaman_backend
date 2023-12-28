<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Micro extends Model
{
    use HasFactory;

    protected $primaryKey = 'micro_id';
     
    protected $fillable = [
        'name',
        'code',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'micros_products', 'micro_id', 'product_id');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dishes_micros', 'micro_id', 'dish_id');
    }

    
}

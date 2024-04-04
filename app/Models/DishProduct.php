<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DishProduct extends Model
{
    use HasFactory;

    protected $table = 'dishes_products';

    protected $primaryKey = 'dish_product_id';

    protected $fillable = [
        'dish_id', 'product_id', 'name', 'weight', 'price', 'kilocalories', 'factor_ids', 'nutrients'
    ];

    public function dish()
    {
        return $this->belongsTo(Dish::class, 'dish_id', 'dish_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
    
}

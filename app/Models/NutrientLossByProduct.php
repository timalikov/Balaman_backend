<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutrientLossByProduct extends Model
{
    use HasFactory;

    protected $table = 'nutrient_losses_by_products';

    protected $fillable = [
        'product_id',
        'factor_id',
        'nutrient_id',
        'coefficient',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function factor()
    {
        return $this->belongsTo(Factor::class, 'factor_id', 'factor_id');
    }

    public function nutrient()
    {
        return $this->belongsTo(Nutrient::class, 'nutrient_id', 'nutrient_id');
    }
    
}

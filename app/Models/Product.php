<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';

    protected $fillable =
    [
        'bls_code',
        'name',
        'description',
        'product_category_id',
        'product_category_code',
        'price',
        
        'kilocalories',
        'image_url',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'product_category_id');
    }
    

    public function nutrients()
    {
        return $this->belongsToMany(Nutrient::class, 'nutrients_products', 'product_id', 'nutrient_id')->withPivot('weight');
    }

    public function factors() {
    return $this->belongsToMany(Factor::class, 'weight_losses', 'product_id', 'factor_id')
                ->withPivot('coefficient');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dishes_products', 'product_id', 'dish_id')
                    ->withPivot(['weight', 'price', 'kilocalories', 'nutrients']);

    }

    public function nutrientLossesByProducts()
    {
        return $this->belongsToMany(
            NutrientLossByProduct::class, 
            'nutrient_losses_by_products',
            'product_id', 
            'factor_id'  
        )->withPivot('coefficient'); 
    }



}

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
        
        'kilocaries',
        'kilocaries_with_fiber',
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
                    ->withPivot(['weight', 'price', 'kilocalories', 'kilocalories_with_fiber', 'nutrients']);

    }

    /**
     * Get the weight loss entries associated with the product.
     */
    public function factorsWithoutPivot()
    {
        return $this->belongsToMany(Factor::class, 'weight_losses', 'product_id', 'factor_id')
                    // Normally, you might use ->withPivot('coefficient') here, but it's omitted to avoid including pivot data
                    ->select(['factors.factor_id', 'factors.name']); // Adjust the selected fields as necessary
    }



}

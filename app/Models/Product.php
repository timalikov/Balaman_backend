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
        'protein',
        'fat',
        'carbohydrate',
        'fiber',
        'total_sugar',
        'saturated_fat',
        'kilocaries',
        'kilocaries_with_fiber',
        'image_url',
        'is_seasonal',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'product_category_id');
    }
    

    public function nutrients()
    {
        return $this->belongsToMany(Nutrient::class, 'nutrients_products', 'product_id', 'nutrient_id');
    }

    public function factors() {
    return $this->belongsToMany(Factor::class, 'weight_losses', 'product_id', 'factor_id')
                ->withPivot('coefficient');
}




}

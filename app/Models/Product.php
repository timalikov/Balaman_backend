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
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'category_id');
    }

    public function micros()
    {
        return $this->belongsToMany(Micro::class, 'micros_products', 'product_id', 'micro_id');
    }

    public function factors() {
    return $this->belongsToMany(Factor::class, 'macros_losses', 'product_id', 'factor_id')
                ->withPivot('coefficient');
}




}

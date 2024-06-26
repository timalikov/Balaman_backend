<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductForMenu extends Model
{
    protected $table = 'products_for_menu';
    protected $primaryKey = 'product_for_menu_id';

    protected $fillable = ['product_id', 'menu_meal_time_id', 'factor_ids', 'brutto_weight', 'netto_weight', 'nutrients'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function menuMealTime()
    {
        return $this->belongsTo(MenuMealTime::class, 'menu_meal_time_id');
    }

    use HasFactory;
}

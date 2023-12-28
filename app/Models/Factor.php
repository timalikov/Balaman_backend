<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factor extends Model
{
    use HasFactory;

    protected $primaryKey = 'factor_id';

    protected $fillable = ['name'];

    public function products() {
    return $this->belongsToMany(Product::class, 'macros_losses', 'factor_id', 'product_id')
                ->withPivot('coefficient');
}

}

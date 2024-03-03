<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nutrient_losses_by_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('product_id');
            $table->unsignedBiginteger('factor_id');
            $table->unsignedBiginteger('nutrient_id');
            $table->float('coefficient', 8, 2);

            $table->foreign('nutrient_id')->references('nutrient_id')->on('nutrients')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('factor_id')->references('factor_id')->on('factors')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrient_losses_by_products');
    }
};

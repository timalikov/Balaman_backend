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
        Schema::create('nutrients_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('nutrient_id');
            $table->unsignedBiginteger('product_id');
            $table->float('weight', 8, 2);

            $table->float('unit', 8, 2)->nullable();
            $table->unsignedBiginteger('unit_type_id')->nullable();

            $table->foreign('nutrient_id')->references('nutrient_id')->on('nutrients')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('unit_type_id')->references('unit_type_id')->on('unit_types')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrients_products');
    }
};

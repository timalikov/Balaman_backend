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
        Schema::create('dishes_products', function (Blueprint $table) {
            $table->unsignedBigInteger('dish_id');
            $table->unsignedBigInteger('product_id');

            $table->float('weight', 8, 2)->default(100);
            $table->float('price');
            $table->float('kilocalories');
            $table->float('kilocalories_with_fiber')->nullable();

            $table->json('nutrients');


            $table->foreign('dish_id')->references('dish_id')->on('dishes')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');


            $table->primary(['dish_id', 'product_id']);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishes_products');
    }
};

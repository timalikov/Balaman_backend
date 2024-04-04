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
            $table->id('dish_product_id');

            $table->unsignedBigInteger('dish_id');
            $table->unsignedBigInteger('product_id');

            $table->string('name');

            $table->float('weight', 8, 2)->default(100);
            $table->float('price');
            $table->float('kilocalories');

            $table->json('factor_ids'); 

            $table->json('nutrients');


            $table->foreign('dish_id')->references('dish_id')->on('dishes')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');




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

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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('bls_code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
    

            //product category
            $table->unsignedBigInteger('product_category_id');
            $table->string('product_category_code')->nullable();

            //price
            $table->float('price');
           
            $table->float('kilocalories');
            $table->float('kilocalories_with_fiber')->nullable();

            $table->string('image_url')->nullable();

            $table->timestamps();

            $table->foreign('product_category_id')->references('product_category_id')->on('product_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

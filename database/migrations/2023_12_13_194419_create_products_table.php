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
            $table->unsignedBigInteger('product_subcategory_id')->nullable();
            $table->unsignedBigInteger('product_subsubcategory_id')->nullable();


            //price
            $table->float('price');

            //macronutrients 
            $table->float('protein');
            $table->float('fat');
            $table->float('carbohydrate');
            $table->float('fiber')->nullable();
            $table->float('total_sugar')->nullable();
            $table->float('saturated_fat')->nullable();
            $table->float('kilocalories');
            $table->float('kilocalories_with_fiber')->nullable();

            $table->string('image_url')->nullable();
            $table->boolean('is_seasonal')->nullable();

            $table->timestamps();

            $table->foreign('product_category_id')->references('category_id')->on('product_categories');
            $table->foreign('product_subcategory_id')->references('subcategory_id')->on('product_subcategories');
            $table->foreign('product_subsubcategory_id')->references('sub_subcategory_id')->on('product_sub_subcategories');
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

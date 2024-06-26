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
        Schema::create('dishes', function (Blueprint $table) {
            $table->id('dish_id');
            $table->string('bls_code')->unique();
            $table->string('name');
            $table->string('description', 5000)->nullable();
            $table->string('recipe_description', 5000)->nullable();

            //dish category
            $table->unsignedBigInteger('dish_category_id');
            $table->string('dish_category_code')->nullable();

            $table->float('price')->default(0);
            $table->float('weight', 8, 2);
            $table->float('kilocalories');


            $table->string('image_url')->nullable();
            $table->boolean('has_relation_with_products')->default(false);
            
            $table->integer('health_factor')->nullable();

            $table->foreign('dish_category_id')->references('dish_category_id')->on('dish_categories')->onDelete('cascade');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};

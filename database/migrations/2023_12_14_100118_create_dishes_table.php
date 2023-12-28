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
            $table->string('description')->nullable();
            $table->string('recipe_description')->nullable();

            //dish category
            $table->unsignedBigInteger('dish_category_id');
            $table->string('dish_category_code')->nullable();

            $table->string('image_url')->nullable();
            $table->boolean('has_relation_with_products')->default(false);
            
            $table->integer('health_factor')->nullable();

            // macronutrients
            $table->float('protein');
            $table->float('fat');
            $table->float('carbohydrate');
            $table->float('fiber')->nullable();
            $table->float('total_sugar')->nullable();
            $table->float('saturated_fat')->nullable();
            $table->float('kilocaries');
            $table->float('kilocaries_with_fiber')->nullable();


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

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
        Schema::create('meal_dishes', function (Blueprint $table) {
            $table->id('meal_dish_id');
            $table->unsignedBiginteger('menu_meal_time_id');
            $table->unsignedBiginteger('dish_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('weight', 8, 2);

            $table->foreign('menu_meal_time_id')->references('menu_meal_time_id')->on('menu_meal_times')->onDelete('cascade');
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
        Schema::dropIfExists('meal_dishes');
    }
};

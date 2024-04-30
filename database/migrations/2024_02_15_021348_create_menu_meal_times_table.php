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
        Schema::create('menu_meal_times', function (Blueprint $table) {
            $table->id('menu_meal_time_id');
            $table->unsignedBiginteger('menu_id');
            // $table->unsignedBiginteger('meal_time_id');
            $table->string('meal_time_name');
            $table->integer('meal_time_number');
            $table->integer('day_of_week');
            $table->integer('week');

            $table->foreign('menu_id')->references('menu_id')->on('menus')->onDelete('cascade');
            // $table->foreign('meal_time_id')->references('meal_time_id')->on('meal_times')->onDelete('cascade');

            $table->timestamps();
        });

        DB::statement('ALTER TABLE menu_meal_times ADD CONSTRAINT chk_day_of_week CHECK (day_of_week >= 1 AND day_of_week <= 6)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_meal_times');
    }
};

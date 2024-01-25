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
        Schema::create('micros_losses_by_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('product_category_id');
            $table->unsignedBiginteger('factor_id');
            $table->unsignedBiginteger('micro_id');
            $table->float('coefficient', 8, 2);

            $table->foreign('micro_id')->references('micro_id')->on('micros')->onDelete('cascade');
            $table->foreign('product_category_id')->references('category_id')->on('product_categories')->onDelete('cascade');
            $table->foreign('factor_id')->references('factor_id')->on('factors')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('micros_losses_by_categories');
    }
};

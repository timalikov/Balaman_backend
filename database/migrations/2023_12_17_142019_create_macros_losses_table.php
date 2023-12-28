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
        Schema::create('macros_losses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('product_id');
            $table->unsignedBiginteger('factor_id');
            $table->float('coefficient', 8, 2);

            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('factor_id')->references('factor_id')->on('factors')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('macros_losses');
    }
};

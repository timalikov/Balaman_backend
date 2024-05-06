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
        Schema::create('products_for_menu', function (Blueprint $table) {
            $table->id('product_for_menu_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('menu_id');
            $table->json('factor_ids');
            $table->float('brutto_weight', 8, 2)->default(100);
            $table->float('netto_weight', 8, 2)->default(100);
            $table->float('kilocalories', 8, 2)->default(0);
            $table->json('nutrients');

            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('menu_id')->references('menu_id')->on('menus')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_for_menu');
    }
};

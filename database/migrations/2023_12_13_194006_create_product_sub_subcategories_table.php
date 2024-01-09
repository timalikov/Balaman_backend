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
        Schema::create('product_sub_subcategories', function (Blueprint $table) {
            $table->id('sub_subcategory_id');
            $table->string('sub_subcategory_code')->unique();
            $table->string('sub_subcategory_name');
            $table->unsignedBigInteger('subcategory_id');

            $table->foreign('subcategory_id')->references('subcategory_id')->on('product_subcategories');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sub_subcategories');
    }
};

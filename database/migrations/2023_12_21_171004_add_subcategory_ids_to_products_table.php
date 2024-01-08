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
        Schema::table('products', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('sub_subcategory_id')->nullable()->after('subcategory_id');

            $table->foreign('subcategory_id')->references('subcategory_id')->on('product_subcategories');   
            $table->foreign('sub_subcategory_id')->references('sub_subcategory_id')->on('product_sub_subcategories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->dropColumn('subcategory_id');
            $table->dropColumn('sub_subcategory_id');
        });
    }
};

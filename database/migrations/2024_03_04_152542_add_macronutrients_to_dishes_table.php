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
        Schema::table('dishes', function (Blueprint $table) {
            $table->decimal('protein', 8, 2)->default(0); // assuming decimal type
            $table->decimal('fat', 8, 2)->default(0);
            $table->decimal('carbohydrate', 8, 2)->default(0);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn(['protein', 'fat', 'carbohydrate']);
        });
        
    }
};

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
        Schema::create('menus', function (Blueprint $table) {
            $table->id('menu_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBiginteger('user_id');
            $table->enum('status', ['active', 'inactive', 'pending', 'archived']); // Пример 
            $table->enum('season', ['spring', 'summer', 'autumn', 'winter']); // Пример

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};

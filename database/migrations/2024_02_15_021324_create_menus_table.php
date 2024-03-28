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
            $table->enum('status', [
                'draft',          // The menu is being edited and is not yet finalized.
                'inactive',       // The menu is not currently in use or active.
                'pending_review', // The menu has been completed by the nutritionist and is awaiting review by the education ministry.
                'under_review',   // The menu is currently being reviewed by the education ministry.
                'needs_revision', // The education ministry has reviewed the menu and requested revisions or improvements.
                'approved',       // The menu has been reviewed and approved by the education ministry.
                'rejected',       // The menu has been reviewed and rejected by the education ministry.
                'archived'        // The menu is no longer active and has been archived.
            ]);

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

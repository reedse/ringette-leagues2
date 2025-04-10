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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'U12 Dragons Red'
            $table->foreignId('association_id')->constrained()->onDelete('cascade');
            $table->foreignId('league_id')->constrained()->onDelete('cascade');
            // season_id is handled via roster_entries
            $table->timestamps();

            // Add index for name as per schema notes
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};

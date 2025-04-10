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
        Schema::create('player_game_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade'); // Track which team the player was playing for
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('shots_on_goal')->default(0);
            $table->integer('saves')->nullable()->default(null); // For goalies
            $table->integer('goals_against')->nullable()->default(null); // For goalies
            $table->timestamps();

            // Unique constraint for player-game combo
            $table->unique(['game_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_game_stats');
    }
};

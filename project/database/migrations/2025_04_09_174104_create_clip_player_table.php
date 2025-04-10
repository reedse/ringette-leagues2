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
        Schema::create('clip_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clip_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->text('coach_note')->nullable(); // Specific note for this sharing instance
            $table->timestamp('sent_at')->nullable(); // When the clip was shared
            $table->timestamps();

            // Unique constraint for clip-player sharing
            $table->unique(['clip_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clip_player');
    }
};

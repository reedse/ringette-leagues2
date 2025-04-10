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
        Schema::create('clips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('coach_user_id')->constrained('users')->onDelete('cascade'); // User who created the clip
            $table->string('clip_title')->nullable();
            $table->string('video_url')->nullable(); // Could inherit from game or be specific
            $table->integer('start_time_seconds')->nullable();
            $table->integer('end_time_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clips');
    }
};

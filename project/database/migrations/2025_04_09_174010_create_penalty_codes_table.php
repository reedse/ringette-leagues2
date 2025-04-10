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
        Schema::create('penalty_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 'TRIP'
            $table->string('name'); // e.g., 'Tripping'
            $table->integer('default_minutes');
            // No timestamps needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_codes');
    }
};

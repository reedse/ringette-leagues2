<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add the type column
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
        });

        // Copy data from name column to type column
        DB::statement('UPDATE subscriptions SET type = name');

        // Make type column required
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('type')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

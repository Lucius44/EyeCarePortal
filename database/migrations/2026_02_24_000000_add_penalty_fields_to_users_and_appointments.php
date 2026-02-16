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
        // 1. Update Users Table (Strikes & Status)
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status')->default('active')->after('role'); // active, restricted, banned
            $table->integer('strikes')->default(0)->after('account_status');
            $table->timestamp('restricted_until')->nullable()->after('strikes'); // For the 1-hour timeout
        });

        // 2. Update Appointments Table (Soft Deletes)
        Schema::table('appointments', function (Blueprint $table) {
            $table->softDeletes(); // Adds 'deleted_at' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_status', 'strikes', 'restricted_until']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
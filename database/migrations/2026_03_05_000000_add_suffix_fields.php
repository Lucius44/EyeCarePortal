<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add suffix to Users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('suffix')->nullable()->after('last_name');
        });

        // 2. Add patient_suffix to Appointments table (for Guests)
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('patient_suffix')->nullable()->after('patient_last_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('suffix');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('patient_suffix');
        });
    }
};
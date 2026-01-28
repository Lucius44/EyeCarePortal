<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // 1. Make user_id optional (nullable)
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // 2. Add columns for Guest details
            $table->string('patient_first_name')->nullable();
            $table->string('patient_middle_name')->nullable(); // Optional Middle Name
            $table->string('patient_last_name')->nullable();
            $table->string('patient_email')->nullable();
            $table->string('patient_phone')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn([
                'patient_first_name', 
                'patient_middle_name', 
                'patient_last_name', 
                'patient_email', 
                'patient_phone'
            ]);
        });
    }
};
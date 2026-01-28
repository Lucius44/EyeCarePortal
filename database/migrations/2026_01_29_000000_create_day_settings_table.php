<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_settings', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // One setting per day
            $table->integer('max_appointments')->default(5); // Default limit is 5
            $table->boolean('is_closed')->default(false); // Default is Open
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_settings');
    }
};
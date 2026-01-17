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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // Link to the User who booked it
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Appointment Details
            $table->string('service');          // e.g., "General Checkup", "Contact Lens Fitting"
            $table->date('appointment_date');   // YYYY-MM-DD
            $table->string('appointment_time'); // e.g., "09:00 AM"
            $table->text('description')->nullable(); // Optional notes
            
            // Status Management
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed, no-show
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

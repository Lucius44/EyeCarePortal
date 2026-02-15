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
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('diagnosis')->nullable()->after('status');    // e.g. "Myopia / Nearsightedness"
            $table->text('prescription')->nullable()->after('diagnosis'); // e.g. "OD: -1.50, OS: -1.75"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['diagnosis', 'prescription']);
        });
    }
};
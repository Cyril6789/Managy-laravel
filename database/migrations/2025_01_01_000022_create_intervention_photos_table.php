<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Photos attached to an intervention (state of the equipment, repair…).
        // Shown on the public tracking page unless flagged "privée".
        Schema::create('intervention_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->boolean('prive')->default(false);   // hidden from the customer page
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_photos');
    }
};

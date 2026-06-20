<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Technician unavailability (congés, maladie, formation…). A technician
        // who is absent over a given period is removed from the pool of available
        // technicians for on-site interventions during that window. Absences are
        // time-precise (datetime range) but can also span whole days.
        Schema::create('technician_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('debut');
            $table->dateTime('fin');
            $table->boolean('journee_entiere')->default(false);
            $table->enum('motif', ['conges', 'maladie', 'formation', 'autre'])->default('conges');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'debut', 'fin']);
            $table->index('debut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_absences');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Standalone calendar events / appointments (ex calendar "rendez-vous").
        // Interventions also appear on the calendar through their rdv_debut/rdv_fin.
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();   // owner / assignee
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->dateTime('debut');
            $table->dateTime('fin')->nullable();
            $table->boolean('journee_entiere')->default(false);
            $table->string('couleur')->default('#2563eb');
            $table->timestamps();

            $table->index('debut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

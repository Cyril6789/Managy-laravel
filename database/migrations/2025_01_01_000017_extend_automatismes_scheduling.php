<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('automatismes', function (Blueprint $table) {
            // Relax the event enum to a plain string (adds "rendez_vous").
            $table->string('evenement')->change();
            // Offset in minutes relative to the appointment (negative = before, positive = after).
            $table->integer('offset_minutes')->nullable()->after('evenement');
            // Optional place filter for appointment-based rules (atelier / domicile).
            $table->string('type_lieu')->nullable()->after('offset_minutes');
        });

        // Records each fired scheduled automatism to avoid duplicate sends.
        Schema::create('automatisme_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automatisme_id')->constrained()->cascadeOnDelete();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->timestamp('ran_at')->nullable();
            $table->unique(['automatisme_id', 'intervention_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automatisme_runs');
        Schema::table('automatismes', function (Blueprint $table) {
            $table->dropColumn(['offset_minutes', 'type_lieu']);
        });
    }
};

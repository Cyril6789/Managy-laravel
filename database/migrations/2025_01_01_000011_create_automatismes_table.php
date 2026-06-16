<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rules that auto-send SMS/email to the customer on intervention events
        // (ex automatismes class). e.g. "on status change to Terminé, send SMS".
        Schema::create('automatismes', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->enum('evenement', [
                'intervention_creee',
                'changement_statut',
                'changement_rdv',
                'commande_recue',
                'sous_traitance_retour',
                'restitution',
            ]);
            $table->foreignId('statut_id')->nullable()->constrained('statuts')->nullOnDelete(); // optional condition
            $table->enum('canal', ['sms', 'email']);
            $table->string('sujet')->nullable();
            $table->text('modele');                 // body template with {placeholders}
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automatismes');
    }
};

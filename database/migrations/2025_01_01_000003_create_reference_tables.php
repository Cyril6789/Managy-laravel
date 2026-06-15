<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Device types (ex "materiels").
        Schema::create('materiels', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->timestamps();
        });

        // Operating systems (ex "se").
        Schema::create('systeme_exploitations', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->timestamps();
        });

        // Antivirus products.
        Schema::create('antivirus', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->timestamps();
        });

        // Configurable intervention statuses (ex "statuts").
        Schema::create('statuts', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('couleur')->default('#64748b');     // UI badge color
            $table->unsignedInteger('ordre')->default(0);
            $table->boolean('verrouille')->default(false);     // ex intervention_occupee: locks editing
            $table->boolean('est_defaut')->default(false);     // status on creation
            $table->boolean('est_cloture')->default(false);    // marks closure
            $table->timestamps();
        });

        // Service catalogue (ex "prestations").
        Schema::create('prestations', function (Blueprint $table) {
            $table->id();
            $table->string('designation');
            $table->decimal('duree_defaut', 6, 2)->default(0); // default hours
            $table->decimal('tarif', 10, 2)->nullable();
            $table->timestamps();
        });

        // Report templates (ex "rapports_types").
        Schema::create('rapport_types', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('texte')->nullable();
            $table->timestamps();
        });

        // Comment templates (ex "commentaires_types").
        Schema::create('commentaire_types', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('texte')->nullable();
            $table->timestamps();
        });

        // Added-material templates (ex "materiels_ajoutes_types").
        Schema::create('materiel_ajoute_types', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('texte')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiel_ajoute_types');
        Schema::dropIfExists('commentaire_types');
        Schema::dropIfExists('rapport_types');
        Schema::dropIfExists('prestations');
        Schema::dropIfExists('statuts');
        Schema::dropIfExists('antivirus');
        Schema::dropIfExists('systeme_exploitations');
        Schema::dropIfExists('materiels');
    }
};

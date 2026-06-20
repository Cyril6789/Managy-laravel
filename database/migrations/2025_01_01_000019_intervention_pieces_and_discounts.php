<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ad-hoc replaced parts billed on an intervention (no catalogue).
        Schema::create('intervention_pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->string('designation');
            $table->decimal('prix', 10, 2)->default(0);   // unit price
            $table->decimal('quantite', 8, 2)->default(1);
            $table->timestamps();
        });

        Schema::table('interventions', function (Blueprint $table) {
            $table->decimal('montant_pieces', 10, 2)->nullable()->after('montant_deplacement');
            // Technician discount captured at restitution (if authorised).
            $table->string('remise_type')->nullable()->after('montant_total');   // euro | pourcent
            $table->decimal('remise_valeur', 10, 2)->nullable()->after('remise_type');
            $table->decimal('remise_montant', 10, 2)->nullable()->after('remise_valeur'); // resolved € amount
        });

        Schema::table('clients', function (Blueprint $table) {
            // Always-free travel for this customer (permission-gated in the UI).
            $table->boolean('deplacement_gratuit')->default(false)->after('siret');
            // Per-customer percentage discounts.
            $table->decimal('remise_prestations', 5, 2)->nullable()->after('deplacement_gratuit');
            $table->decimal('remise_pieces', 5, 2)->nullable()->after('remise_prestations');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['deplacement_gratuit', 'remise_prestations', 'remise_pieces']);
        });
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn(['montant_pieces', 'remise_type', 'remise_valeur', 'remise_montant']);
        });
        Schema::dropIfExists('intervention_pieces');
    }
};

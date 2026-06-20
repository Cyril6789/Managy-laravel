<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            // Workshop workflow: the technician marks the repair done ("finalisée")
            // before the customer comes to pick the device up ("restituer & clôturer").
            $table->timestamp('finalisee_at')->nullable()->after('restituted_at');

            // Billing snapshot captured at restitution / signature.
            $table->decimal('montant_prestations', 10, 2)->nullable()->after('tarif_estimatif');
            $table->decimal('montant_deplacement', 10, 2)->nullable()->after('montant_prestations');
            $table->decimal('deplacement_km', 8, 2)->nullable()->after('montant_deplacement');
            $table->decimal('montant_total', 10, 2)->nullable()->after('deplacement_km');
            $table->decimal('montant_paye', 10, 2)->nullable()->after('montant_total');
            // espèces | cb | cheque | virement | autre
            $table->string('paiement_mode')->nullable()->after('montant_paye');
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn([
                'finalisee_at', 'montant_prestations', 'montant_deplacement',
                'deplacement_km', 'montant_total', 'montant_paye', 'paiement_mode',
            ]);
        });
    }
};

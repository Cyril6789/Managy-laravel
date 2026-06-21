<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Part of the service hours can be settled from the customer's
        // maintenance pack (never the parts nor the travel). We record how many
        // hours were drawn and the matching euro value deducted from the total.
        Schema::table('interventions', function (Blueprint $table) {
            $table->decimal('maintenance_heures', 8, 2)->nullable()->after('montant_paye');
            $table->decimal('montant_maintenance', 10, 2)->nullable()->after('maintenance_heures');
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn(['maintenance_heures', 'montant_maintenance']);
        });
    }
};

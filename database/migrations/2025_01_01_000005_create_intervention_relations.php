<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff assignment / "prise en charge" (ex prise_en_charge).
        Schema::create('intervention_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->unique(['intervention_id', 'user_id']);
        });

        // Services rendered on an intervention (ex prestations_effectuees).
        Schema::create('intervention_prestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prestation_id')->nullable()->constrained('prestations')->nullOnDelete();
            $table->string('designation');
            $table->decimal('duree', 6, 2)->default(0);     // hours
            $table->decimal('tarif', 10, 2)->nullable();
            $table->timestamps();
        });

        // Supplier orders attached to an intervention (ex commandes).
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->string('fournisseur')->nullable();
            $table->string('bon_commande')->nullable();      // ex bdc
            $table->string('numero_commande')->nullable();
            $table->string('suivi_colis')->nullable();
            $table->date('commande_le')->nullable();
            $table->date('recue_le')->nullable();
            $table->boolean('recue')->default(false);
            $table->timestamps();
        });

        // Subcontracting / "sous-traitance" (ex sous_traitances).
        Schema::create('sous_traitances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->string('nom')->nullable();
            $table->string('devis')->nullable();
            $table->string('numero_commande')->nullable();
            $table->string('suivi_aller')->nullable();
            $table->string('suivi_retour')->nullable();
            $table->date('envoye_le')->nullable();
            $table->date('retour_le')->nullable();
            $table->boolean('retournee')->default(false);
            $table->timestamps();
        });

        // Internal team chat within an intervention (ex chat_inter).
        Schema::create('intervention_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->timestamps();
        });

        // Activity log per intervention (ex logs).
        Schema::create('intervention_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('texte');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_logs');
        Schema::dropIfExists('intervention_messages');
        Schema::dropIfExists('sous_traitances');
        Schema::dropIfExists('commandes');
        Schema::dropIfExists('intervention_prestations');
        Schema::dropIfExists('intervention_user');
    }
};

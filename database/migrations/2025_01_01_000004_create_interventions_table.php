<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Core business entity: an IT service job/ticket (ex "interventions").
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable()->index();   // e.g. 2026-0042
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('materiel_id')->nullable()->constrained('materiels')->nullOnDelete();
            $table->foreignId('systeme_exploitation_id')->nullable()->constrained('systeme_exploitations')->nullOnDelete();
            $table->foreignId('antivirus_id')->nullable()->constrained('antivirus')->nullOnDelete();
            $table->foreignId('statut_id')->nullable()->constrained('statuts')->nullOnDelete();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restituted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('type_lieu', ['atelier', 'domicile'])->default('atelier'); // ex type_atelier_rdv
            $table->dateTime('rdv_debut')->nullable();
            $table->dateTime('rdv_fin')->nullable();
            $table->boolean('rdv_annule')->default(false);

            $table->unsignedTinyInteger('priorite')->default(0);
            $table->boolean('urgente')->default(false);
            $table->boolean('garantie')->default(false);

            $table->text('materiel_depose')->nullable();  // ex matos
            $table->text('panne')->nullable();            // problem reported
            $table->text('diagnostic')->nullable();       // ex resolution (technical report)
            $table->text('materiel_ajoute')->nullable();
            $table->text('message_client')->nullable();   // customer-facing message
            $table->text('message_interne')->nullable();  // staff-only notes
            $table->text('mdp')->nullable();              // access codes (sensitive)
            $table->decimal('tarif_estimatif', 10, 2)->nullable();
            $table->text('note')->nullable();

            $table->boolean('facturee')->default(false);
            $table->boolean('payee')->default(false);

            $table->string('public_token', 64)->unique();   // secure customer live link (ex external_link)

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();     // ex time_cloture (null = open)
            $table->timestamp('restituted_at')->nullable();
            $table->timestamps();

            $table->index('closed_at');
            $table->index('rdv_debut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Outbound SMS / email log to customers (ex sms, mails_differes, mails_with_ack).
        Schema::create('client_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('intervention_id')->nullable()->constrained('interventions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('canal', ['sms', 'email']);
            $table->string('destinataire');
            $table->string('sujet')->nullable();
            $table->text('corps');
            $table->enum('statut', ['programme', 'envoye', 'echec'])->default('envoye');
            $table->timestamp('programme_pour')->nullable();   // deferred sending
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_messages');
    }
};

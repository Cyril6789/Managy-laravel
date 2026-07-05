<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "Assistance" tickets: each user of a société can open a request
        // (question / bug / suggestion) and follow its progress. The platform
        // super-admin supervises every société's tickets from /admin/assistance.
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // opener
            $table->string('sujet');
            $table->text('description');
            $table->string('type')->default('question');       // question | bug | suggestion
            $table->string('urgence')->default('normale');      // basse | normale | haute | urgente
            $table->string('statut')->default('ouvert');        // ouvert | en_cours | en_attente | resolu | ferme
            $table->timestamp('last_reply_at')->nullable();     // drives "latest activity" ordering
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'statut']);
        });

        // The two-way conversation attached to a ticket. `is_staff` marks a reply
        // written by the platform support (super-admin) as opposed to the société.
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // author
            $table->text('body');
            $table->boolean('is_staff')->default(false);
            $table->timestamps();

            $table->index('support_ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
    }
};

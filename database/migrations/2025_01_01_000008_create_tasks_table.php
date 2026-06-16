<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // To-do tasks with optional time tracking (ex tasks module + heures stats).
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();        // assignee
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('intervention_id')->nullable()->constrained('interventions')->nullOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('statut', ['a_faire', 'en_cours', 'terminee'])->default('a_faire');
            $table->unsignedTinyInteger('priorite')->default(0);
            $table->decimal('heures_estimees', 6, 2)->nullable();
            $table->decimal('heures_passees', 6, 2)->nullable();
            $table->date('echeance')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

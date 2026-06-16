<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Post-intervention customer satisfaction surveys (ex satisfaction).
        Schema::create('satisfactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->nullable()->constrained('interventions')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->unsignedTinyInteger('note')->nullable();   // 1..5
            $table->text('commentaire')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satisfactions');
    }
};

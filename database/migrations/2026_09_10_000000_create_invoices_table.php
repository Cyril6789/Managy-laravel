<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('intervention_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number');
            $table->date('issued_at');
            $table->json('issuer');
            $table->json('customer');
            $table->json('lines');
            $table->decimal('subtotal_ht', 12, 2);
            $table->decimal('total_ht', 12, 2);
            $table->string('currency', 3)->default('EUR');
            $table->text('legal_notice');
            $table->string('pdf_path');
            $table->timestamps();

            $table->unique(['society_id', 'number']);
            $table->index(['society_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

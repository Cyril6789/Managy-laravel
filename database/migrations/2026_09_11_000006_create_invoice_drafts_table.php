<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->cascadeOnDelete();
            $table->foreignId('intervention_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('lines');
            $table->string('total_discount_type', 20)->default('euro');
            $table->decimal('total_discount_value', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['society_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_drafts');
    }
};

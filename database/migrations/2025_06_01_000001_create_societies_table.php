<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "société" is the SaaS tenant. Every registration creates one and its
     * id is propagated to every business table (clients, interventions, ...).
     * It also holds the company identity that used to live in the key/value
     * settings (name, SIRET, logo, ...).
     */
    public function up(): void
    {
        Schema::create('societies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('siret')->nullable();
            $table->string('vat')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('societies');
    }
};

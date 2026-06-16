<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customers (ex "clients"). Companies can have child contacts via parent_id.
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['professionnel', 'particulier'])->default('professionnel');
            $table->string('civilite')->nullable();          // M., Mme, Sté
            $table->string('nom');                            // last name or company name
            $table->string('prenom')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone_fixe')->nullable();
            $table->string('telephone_mobile')->nullable();
            $table->string('adresse')->nullable();
            $table->string('adresse_complement')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('ville')->nullable();
            $table->string('siret')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('nom');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

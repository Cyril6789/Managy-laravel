<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A "contact" is no longer a separate kind of record: it is a regular
     * "particulier" client that may, in addition to its own interventions, play
     * the contact role for one or several companies. This replaces the single
     * parent_id link with a many-to-many pivot.
     */
    public function up(): void
    {
        Schema::create('company_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('clients')->cascadeOnDelete();   // professionnel
            $table->foreignId('contact_id')->constrained('clients')->cascadeOnDelete();    // particulier
            $table->timestamps();
            $table->unique(['company_id', 'contact_id']);
        });

        // Migrate existing parent_id relationships into the pivot.
        DB::table('clients')->whereNotNull('parent_id')->orderBy('id')
            ->each(function ($client) {
                DB::table('company_contact')->insertOrIgnore([
                    'company_id' => $client->parent_id,
                    'contact_id' => $client->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_contact');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('public_token');
            $table->string('signataire_nom')->nullable()->after('signature_path');
            $table->timestamp('signed_at')->nullable()->after('signataire_nom');
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn(['signature_path', 'signataire_nom', 'signed_at']);
        });
    }
};

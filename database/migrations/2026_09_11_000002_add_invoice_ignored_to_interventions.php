<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->timestamp('invoice_ignored_at')->nullable()->after('facturee');
            $table->foreignId('invoice_ignored_by')->nullable()->after('invoice_ignored_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_ignored_by');
            $table->dropColumn('invoice_ignored_at');
        });
    }
};

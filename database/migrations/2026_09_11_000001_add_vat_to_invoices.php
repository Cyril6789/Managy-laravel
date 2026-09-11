<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('vat_enabled')->default(false)->after('total_ht');
            $table->decimal('vat_rate', 5, 2)->default(0)->after('vat_enabled');
            $table->decimal('vat_amount', 12, 2)->default(0)->after('vat_rate');
            $table->decimal('total_ttc', 12, 2)->default(0)->after('vat_amount');
        });

        DB::table('invoices')->update(['total_ttc' => DB::raw('total_ht')]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['vat_enabled', 'vat_rate', 'vat_amount', 'total_ttc']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users now belong to a société (null for the platform super-admin).
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('society_id')->nullable()->after('id')
                ->constrained('societies')->nullOnDelete();
            // Platform owner: supervises every société, belongs to none.
            $table->boolean('is_super_admin')->default(false)->after('is_admin');
        });

        // Login is e-mail only now: drop the unique pseudo handle requirement.
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_pseudo_unique');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('pseudo')->nullable()->change();
        });

        // Settings become per-société (sms, smtp, automation, billing, ...).
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('society_id')->nullable()->after('id')
                ->constrained('societies')->cascadeOnDelete();
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_unique');
            $table->unique(['society_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['society_id', 'key']);
            $table->dropConstrainedForeignId('society_id');
            $table->unique('key');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('society_id');
            $table->dropColumn('is_super_admin');
            $table->string('pseudo')->nullable(false)->change();
            $table->unique('pseudo');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Staff / technicians (ex "staffs"). Single-tenant: no compte_principal.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('prenom')->nullable();
            $table->string('nom');
            $table->string('pseudo')->unique();              // login handle
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_admin')->default(false);      // ex "gerant": full access
            $table->boolean('is_active')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('chat_status')->default('online'); // presence
            $table->json('preferences')->nullable();          // theme, etc.
            $table->timestamp('last_action_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Granular per-feature permissions (replaces legacy rights_staff modul|num).
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission');
            $table->primary(['user_id', 'permission']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

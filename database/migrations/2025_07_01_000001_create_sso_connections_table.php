<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One SSO configuration per société and per provider (microsoft / google).
        // The gérant enables it and stores the provider's OAuth credentials so
        // their team can sign in with Microsoft Entra or Google Workspace.
        Schema::create('sso_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('provider');                       // microsoft | google
            $table->boolean('enabled')->default(false);
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable();        // encrypted at rest
            $table->string('tenant_id')->nullable();          // Entra directory (tenant) id
            $table->string('allowed_domains')->nullable();    // comma-separated e-mail domains
            // Create the Managy user on the first SSO login when it does not exist yet.
            $table->boolean('auto_provision_users')->default(true);
            $table->timestamps();

            $table->unique(['society_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_connections');
    }
};

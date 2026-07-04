<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Link a Managy account to its identity in the SSO provider so that later
        // logins resolve to the same user even if the e-mail address changes.
        Schema::table('users', function (Blueprint $table) {
            $table->string('sso_provider')->nullable()->after('two_factor_enabled');
            $table->string('sso_subject')->nullable()->after('sso_provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_provider', 'sso_subject']);
        });
    }
};

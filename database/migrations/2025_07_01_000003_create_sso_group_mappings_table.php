<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bridges an SSO security group (from the tenant's directory) to a Managy
        // permission group. When a user signs in via SSO, every mapping whose
        // external group matches one of the user's directory groups places them in
        // the corresponding Managy group. "external_group" holds either the group's
        // object id (GUID) or its name (e.g. "sg_managy_tech_niv1").
        Schema::create('sso_group_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('permission_group_id')->constrained('permission_groups')->cascadeOnDelete();
            $table->string('external_group');
            $table->timestamps();

            $table->unique(['permission_group_id', 'external_group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_group_mappings');
    }
};

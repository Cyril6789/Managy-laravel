<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Optional layer on top of the flat per-user permissions: the gérant can
        // bundle permissions into named groups (e.g. "Tech_Niv1") and assign users
        // to them instead of ticking every permission on every user.
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['society_id', 'name']);
        });

        // Permissions granted by a group (values from App\Support\Permissions).
        Schema::create('permission_group_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_group_id')->constrained('permission_groups')->cascadeOnDelete();
            $table->string('permission');

            // Custom index name: the auto-generated one
            // ("permission_group_permissions_permission_group_id_permission_unique")
            // exceeds MySQL/MariaDB's 64-character identifier limit.
            $table->unique(['permission_group_id', 'permission'], 'perm_group_permissions_unique');
        });

        // Group membership. "source" tells manual assignments apart from the ones
        // maintained automatically from the SSO security groups, so a re-sync only
        // ever touches the SSO-managed rows.
        Schema::create('permission_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_group_id')->constrained('permission_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('source')->default('manual');       // manual | sso
            $table->timestamps();

            $table->unique(['permission_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_group_user');
        Schema::dropIfExists('permission_group_permissions');
        Schema::dropIfExists('permission_groups');
    }
};

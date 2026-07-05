<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the activity log into a full audit trail and enables soft deletes on
 * every audited business model so that deletions can be reviewed and undone.
 */
return new class extends Migration
{
    /**
     * Every model that becomes auditable + soft-deletable. Table names are read
     * from the models themselves to avoid any pluralisation guesswork.
     *
     * @return list<class-string<\Illuminate\Database\Eloquent\Model>>
     */
    private function auditedModels(): array
    {
        return [
            \App\Models\Client::class,
            \App\Models\Intervention::class,
            \App\Models\Commande::class,
            \App\Models\SousTraitance::class,
            \App\Models\InterventionPrestation::class,
            \App\Models\InterventionPiece::class,
            \App\Models\InterventionPhoto::class,
            \App\Models\InterventionMessage::class,
            \App\Models\ClientMessage::class,
            \App\Models\PublicMessage::class,
            \App\Models\Task::class,
            \App\Models\Event::class,
            \App\Models\Materiel::class,
            \App\Models\StickyNote::class,
            \App\Models\Automatisme::class,
            \App\Models\PermissionGroup::class,
            \App\Models\TechnicianAbsence::class,
            \App\Models\MaintenanceMovement::class,
            \App\Models\Satisfaction::class,
            \App\Models\Antivirus::class,
            \App\Models\SystemeExploitation::class,
            \App\Models\SsoConnection::class,
            \App\Models\User::class,
            \App\Models\Statut::class,
            \App\Models\Prestation::class,
            \App\Models\CommentaireType::class,
            \App\Models\MaterielAjouteType::class,
            \App\Models\MessageType::class,
            \App\Models\RapportType::class,
        ];
    }

    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->json('changes')->nullable()->after('description');
            $table->timestamp('undone_at')->nullable();
            $table->unsignedBigInteger('undone_by')->nullable();
        });

        foreach ($this->auditedModels() as $class) {
            $table = (new $class)->getTable();

            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }

        // A soft-deleted user keeps its row, so the DB-level unique would block
        // re-registering the same address. Uniqueness is now enforced at
        // validation, scoped to non-deleted rows (see the auth controllers).
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });

        foreach ($this->auditedModels() as $class) {
            $table = (new $class)->getTable();

            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['changes', 'undone_at', 'undone_by']);
        });
    }
};

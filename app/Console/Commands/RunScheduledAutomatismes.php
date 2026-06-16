<?php

namespace App\Console\Commands;

use App\Models\Automatisme;
use App\Models\AutomatismeRun;
use App\Models\Intervention;
use App\Services\AutomatismeRunner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('managy:run-automatismes')]
#[Description('Déclenche les automatismes planifiés autour des rendez-vous (rappels, satisfaction…)')]
class RunScheduledAutomatismes extends Command
{
    public function handle(AutomatismeRunner $runner): int
    {
        $rules = Automatisme::where('actif', true)->where('evenement', 'rendez_vous')->get();
        $sent = 0;

        foreach ($rules as $rule) {
            $offset = (int) $rule->offset_minutes;

            // target = rdv_debut + offset  must fall in the last 2h window (grace for missed runs).
            $start = now()->subHours(2)->subMinutes($offset);
            $end = now()->subMinutes($offset);

            $query = Intervention::whereNotNull('rdv_debut')
                ->where('rdv_annule', false)
                ->whereBetween('rdv_debut', [$start, $end])
                ->when($rule->type_lieu, fn ($q) => $q->where('type_lieu', $rule->type_lieu))
                ->when($rule->statut_id, fn ($q) => $q->where('statut_id', $rule->statut_id));

            foreach ($query->get() as $intervention) {
                $already = AutomatismeRun::where('automatisme_id', $rule->id)
                    ->where('intervention_id', $intervention->id)->exists();
                if ($already) {
                    continue;
                }

                $runner->fireRule($rule, $intervention);
                AutomatismeRun::create([
                    'automatisme_id' => $rule->id,
                    'intervention_id' => $intervention->id,
                    'ran_at' => now(),
                ]);
                $sent++;
            }
        }

        $this->info("Automatismes planifiés déclenchés : {$sent}");

        return self::SUCCESS;
    }
}

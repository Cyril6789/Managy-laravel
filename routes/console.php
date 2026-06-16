<?php

use App\Console\Commands\RunScheduledAutomatismes;
use Illuminate\Support\Facades\Schedule;

// Fire appointment-based automatisms (reminders, satisfaction…) every 5 minutes.
Schedule::command(RunScheduledAutomatismes::class)->everyFiveMinutes()->withoutOverlapping();

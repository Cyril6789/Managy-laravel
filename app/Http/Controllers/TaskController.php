<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Support\Permissions;

class TaskController extends Controller
{
    public function index()
    {
        $this->authorize(Permissions::TASKS_VIEW);

        // The list, filters and create/complete/delete actions live in the
        // <livewire:tasks /> component rendered by this view.
        return view('tasks.index');
    }

    /** Used by the dashboard widget to tick a task off without leaving the page. */
    public function toggle(Task $task)
    {
        $this->authorize(Permissions::TASKS_MANAGE);

        $done = $task->statut === 'terminee';
        $task->update([
            'statut' => $done ? 'a_faire' : 'terminee',
            'completed_at' => $done ? null : now(),
        ]);

        return back();
    }
}

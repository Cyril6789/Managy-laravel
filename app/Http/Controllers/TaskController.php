<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permissions::TASKS_VIEW);

        $tasks = Task::with(['user', 'client'])
            ->when($request->input('statut'), fn ($q, $s) => $q->where('statut', $s))
            ->when($request->input('mine'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->orderByRaw("CASE statut WHEN 'terminee' THEN 1 ELSE 0 END")
            ->orderByRaw('CASE WHEN echeance IS NULL THEN 1 ELSE 0 END, echeance')
            ->orderByDesc('priorite')
            ->paginate(25)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'techniciens' => User::where('is_active', true)->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize(Permissions::TASKS_MANAGE);

        Task::create($this->rules($request) + ['created_by' => $request->user()->id]);

        return back()->with('success', 'Tâche créée.');
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize(Permissions::TASKS_MANAGE);

        $data = $this->rules($request);
        $data['completed_at'] = $data['statut'] === 'terminee' ? ($task->completed_at ?? now()) : null;
        $task->update($data);

        return back()->with('success', 'Tâche mise à jour.');
    }

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

    public function destroy(Task $task)
    {
        $this->authorize(Permissions::TASKS_MANAGE);

        $task->delete();

        return back()->with('success', 'Tâche supprimée.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'intervention_id' => ['nullable', 'exists:interventions,id'],
            'statut' => ['required', Rule::in(['a_faire', 'en_cours', 'terminee'])],
            'priorite' => ['nullable', 'integer', 'between:0,3'],
            'heures_estimees' => ['nullable', 'numeric', 'min:0'],
            'heures_passees' => ['nullable', 'numeric', 'min:0'],
            'echeance' => ['nullable', 'date'],
        ]);
    }
}

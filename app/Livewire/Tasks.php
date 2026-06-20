<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tasks list with inline create / complete / delete — no page reload.
 * Replaces the previous full-form-POST page.
 */
class Tasks extends Component
{
    use WithPagination;

    public string $filtre = '';   // '' | a_faire | en_cours | terminee

    public bool $mine = false;

    public bool $showModal = false;

    public array $form = [
        'titre' => '',
        'user_id' => '',
        'echeance' => '',
        'heures_estimees' => '',
        'description' => '',
    ];

    public function mount(): void
    {
        Gate::authorize(Permissions::TASKS_VIEW);
        $this->form['user_id'] = (string) Auth::id();
    }

    public function updating($name): void
    {
        if (in_array($name, ['filtre', 'mine'], true)) {
            $this->resetPage();
        }
    }

    public function create(): void
    {
        Gate::authorize(Permissions::TASKS_MANAGE);

        // Accept French-style decimals (comma) for the estimated hours.
        $this->form['heures_estimees'] = is_string($this->form['heures_estimees'])
            ? str_replace([' ', ','], ['', '.'], trim($this->form['heures_estimees']))
            : $this->form['heures_estimees'];

        $data = $this->validate([
            'form.titre' => ['required', 'string', 'max:255'],
            'form.user_id' => ['nullable', 'exists:users,id'],
            'form.echeance' => ['nullable', 'date'],
            'form.heures_estimees' => ['nullable', 'numeric', 'min:0'],
            'form.description' => ['nullable', 'string'],
        ])['form'];

        Task::create([
            'titre' => $data['titre'],
            'user_id' => $data['user_id'] ?: null,
            'echeance' => $data['echeance'] ?: null,
            'heures_estimees' => $data['heures_estimees'] !== '' ? $data['heures_estimees'] : null,
            'description' => $data['description'] ?: null,
            'statut' => 'a_faire',
            'created_by' => Auth::id(),
        ]);

        $this->reset('form');
        $this->form['user_id'] = (string) Auth::id();
        $this->showModal = false;
    }

    public function toggle(int $id): void
    {
        Gate::authorize(Permissions::TASKS_MANAGE);

        $task = Task::findOrFail($id);
        $done = $task->statut === 'terminee';
        $task->update([
            'statut' => $done ? 'a_faire' : 'terminee',
            'completed_at' => $done ? null : now(),
        ]);
    }

    public function delete(int $id): void
    {
        Gate::authorize(Permissions::TASKS_MANAGE);

        Task::findOrFail($id)->delete();
    }

    public function render()
    {
        $tasks = Task::with(['user', 'client'])
            ->when($this->filtre !== '', fn ($q) => $q->where('statut', $this->filtre))
            ->when($this->mine, fn ($q) => $q->where('user_id', Auth::id()))
            ->orderByRaw("CASE statut WHEN 'terminee' THEN 1 ELSE 0 END")
            ->orderByRaw('CASE WHEN echeance IS NULL THEN 1 ELSE 0 END, echeance')
            ->orderByDesc('priorite')
            ->paginate(25);

        return view('livewire.tasks', [
            'tasks' => $tasks,
            'techniciens' => User::where('is_active', true)->orderBy('nom')->get(),
        ]);
    }
}

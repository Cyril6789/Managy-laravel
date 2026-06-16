@props(['icon' => 'dot', 'title' => 'Rien à afficher', 'message' => null])

<div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
        <x-icon :name="$icon" class="h-6 w-6" />
    </div>
    <p class="font-medium text-gray-700 dark:text-gray-300">{{ $title }}</p>
    @if ($message)<p class="mt-1 max-w-sm text-sm text-gray-400">{{ $message }}</p>@endif
    @isset($action)<div class="mt-4">{{ $action }}</div>@endisset
</div>

@props([
    'target',                 // id of the textarea to fill
    'items' => [],            // list of models exposing the text via $field
    'field' => 'texte',
    'labelField' => 'titre',
    'mode' => 'replace',      // replace | append
    'placeholder' => 'Insérer un modèle…',
])

@if (count($items))
    <select onchange="fillTextarea('{{ $target }}', this.value, '{{ $mode }}'); this.selectedIndex = 0;"
            class="rounded-lg border-gray-300 py-1 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
        <option value="">{{ $placeholder }}</option>
        @foreach ($items as $item)
            <option value="{{ $item->{$field} }}">{{ \Illuminate\Support\Str::limit($item->{$labelField}, 40) }}</option>
        @endforeach
    </select>
@endif

@props([
    'name',
    'options' => [],            // assoc [value => label] OR list of ['value','label']
    'selected' => null,
    'placeholder' => '— Sélectionner —',
    'searchPlaceholder' => 'Rechercher…',   // kept for backward compatibility (unused)
    'allowEmpty' => true,
])

@php
    $list = [];
    foreach ($options as $key => $val) {
        if (is_array($val)) {
            $list[] = ['value' => (string) $val['value'], 'label' => (string) $val['label']];
        } else {
            $list[] = ['value' => (string) $key, 'label' => (string) $val];
        }
    }
    $selected = $selected === null ? '' : (string) $selected;
@endphp

{{--
    Native <select>: on tablets/phones (iPad) this uses the OS picker, which is
    always scrollable and far smoother than a custom JS dropdown. The previous
    Alpine-based searchable dropdown could not be scrolled by touch on iPadOS.
--}}
<select name="{{ $name }}" id="{{ $name }}"
        {{ $attributes->only('class')->merge([
             'class' => 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100',
        ]) }}>
    @if ($allowEmpty)
        <option value="" @selected($selected === '')>{{ $placeholder }}</option>
    @elseif ($selected === '')
        <option value="" disabled selected>{{ $placeholder }}</option>
    @endif
    @foreach ($list as $opt)
        <option value="{{ $opt['value'] }}" @selected($opt['value'] === $selected)>{{ $opt['label'] }}</option>
    @endforeach
</select>

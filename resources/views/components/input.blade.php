@props(['name' => null, 'type' => 'text'])

<input type="{{ $type }}"
       @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
       {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100',
       ]) }} />

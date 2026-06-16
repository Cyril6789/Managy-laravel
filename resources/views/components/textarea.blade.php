@props(['name' => null, 'rows' => 3])

<textarea @if ($name) name="{{ $name }}" id="{{ $name }}" @endif rows="{{ $rows }}"
          {{ $attributes->merge([
               'class' => 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100',
          ]) }}>{{ $slot }}</textarea>

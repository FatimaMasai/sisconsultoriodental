@props(['color' => 'gray'])

@php
    // Tonos suaves/pastel: fondo muy claro y texto en un tono medio, nada saturado.
    $colors = [
        'gray' => 'bg-gray-50 text-gray-500 dark:bg-gray-700/60 dark:text-gray-300',
        'green' => 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
        'red' => 'bg-red-50 text-red-500 dark:bg-red-900/20 dark:text-red-400',
        'yellow' => 'bg-yellow-50 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
        'blue' => 'bg-blue-50 text-blue-500 dark:bg-blue-900/20 dark:text-blue-400',
    ];

    $classes = $colors[$color] ?? $colors['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap {$classes}"]) }}>
    {{ $slot }}
</span>

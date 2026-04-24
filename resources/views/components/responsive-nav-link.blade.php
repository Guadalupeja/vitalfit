@props(['active'])

@php
$classes = ($active ?? false)
    ? 'vf-tab-active block w-full rounded-md px-3 py-2 text-start text-base font-medium transition'
    : 'vf-tab-inactive block w-full rounded-md px-3 py-2 text-start text-base font-medium transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
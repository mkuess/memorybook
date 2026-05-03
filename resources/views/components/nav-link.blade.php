@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-brand-600 text-sm font-medium leading-5 text-[#2F2E2A] focus:outline-none focus:border-brand-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[#706B62] hover:text-[#2F2E2A] hover:border-[#DDD6CA] focus:outline-none focus:text-[#2F2E2A] focus:border-[#DDD6CA] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

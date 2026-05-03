@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-600 text-start text-base font-medium text-brand-700 bg-brand-50 focus:outline-none focus:text-brand-800 focus:bg-brand-100 focus:border-brand-700 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[#706B62] hover:text-[#2F2E2A] hover:bg-[#EFEAE1] hover:border-[#DDD6CA] focus:outline-none focus:text-[#2F2E2A] focus:bg-[#EFEAE1] focus:border-[#DDD6CA] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

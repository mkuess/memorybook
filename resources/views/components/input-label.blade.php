@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#2F2E2A]']) }}>
    {{ $value ?? $slot }}
</label>

@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-[#DDD6CA] focus:border-brand-600 focus:ring-brand-600 rounded-md shadow-sm']) }}>

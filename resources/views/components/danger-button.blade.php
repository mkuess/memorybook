<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#9A4F3F] border border-transparent rounded font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#7E3F31] active:bg-[#6B3429] focus:outline-none focus:ring-2 focus:ring-[#9A4F3F] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

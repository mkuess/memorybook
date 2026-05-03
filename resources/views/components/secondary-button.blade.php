<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-[#EFEAE1] border border-[#DDD6CA] rounded font-semibold text-xs text-[#2F2E2A] uppercase tracking-widest hover:bg-[#D8D2C8] focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

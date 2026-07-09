<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-transparent bg-primary px-5 py-3 font-display text-label-md text-white shadow-ai transition duration-200 hover:bg-primary-container active:scale-[0.98] rh-focus']) }}>
    {{ $slot }}
</button>

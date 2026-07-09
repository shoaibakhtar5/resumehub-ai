<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-outline-variant/40 bg-surface-container px-5 py-3 font-display text-label-md text-on-surface transition duration-200 hover:bg-surface-container-high active:scale-[0.98] disabled:opacity-50 rh-focus']) }}>
    {{ $slot }}
</button>

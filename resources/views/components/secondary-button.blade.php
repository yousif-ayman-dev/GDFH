<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-[rgb(var(--color-surface))] border border-[rgb(var(--color-border))] rounded-md font-semibold text-xs text-[rgb(var(--color-text-primary))] uppercase tracking-widest shadow-sm hover:bg-[rgb(var(--color-surface-soft))] focus:outline-none disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

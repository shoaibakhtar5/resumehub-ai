@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rh-input disabled:cursor-not-allowed disabled:opacity-60']) }}>

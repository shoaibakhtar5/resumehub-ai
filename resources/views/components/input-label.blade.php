@props(['value'])

<label {{ $attributes->merge(['class' => 'rh-label']) }}>
    {{ $value ?? $slot }}
</label>

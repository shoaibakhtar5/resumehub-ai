@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'error' => null,
])

@php
    $fieldId = $id ?? $name;
@endphp

<div>
    @if ($label)
        <label for="{{ $fieldId }}" class="rh-label mb-2">{{ $label }}</label>
    @endif
    <input id="{{ $fieldId }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'rh-input']) }}>
    @if ($error)
        <p class="mt-2 text-body-sm text-danger">{{ $error }}</p>
    @endif
    {{ $slot }}
</div>

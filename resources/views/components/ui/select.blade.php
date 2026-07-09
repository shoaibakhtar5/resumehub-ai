@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'options' => [],
    'selected' => null,
])

@php
    $fieldId = $id ?? $name;
@endphp

<div>
    @if ($label)
        <label for="{{ $fieldId }}" class="rh-label mb-2">{{ $label }}</label>
    @endif
    <select id="{{ $fieldId }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'rh-input']) }}>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>

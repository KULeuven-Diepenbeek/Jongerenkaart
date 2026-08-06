@props([
    'name',
    'label',
    'value' => null,
    'options' => [],
])

@php
    $value = old($name, $value);
    $hasValueInOptions = $value === null || $value === '' || in_array($value, $options, true);
@endphp

<label class="manage-field" data-choice-wrapper>
    <span>{{ $label }}</span>
    <select name="{{ $name }}" data-choice-select>
        <option value="">Geen keuze</option>
        @foreach ($options as $option)
            <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
        @endforeach
        <option value="__other" @selected(! $hasValueInOptions)>Andere</option>
    </select>
    <input
        type="text"
        name="{{ $name }}_other"
        value="{{ ! $hasValueInOptions ? $value : '' }}"
        placeholder="Andere waarde"
        data-choice-other
        @if ($hasValueInOptions) hidden @endif
    >
</label>

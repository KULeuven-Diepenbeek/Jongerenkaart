@props([
    'field',
    'label',
    'values' => [],
    'options' => [],
])

@php
    $otherField = str_replace(['][', '[', ']'], ['_', '_', ''], $field).'_other';
    $values = collect($values)->filter()->values();
    if ($values->isEmpty()) {
        $values = collect(['']);
    }
@endphp

<div class="manage-repeat-choice">
    <h2>{{ $label }}</h2>
    <div data-repeat-list="{{ $field }}" class="repeat-list">
        @foreach ($values as $index => $value)
            @php
                $hasValueInOptions = $value === '' || in_array($value, $options, true);
            @endphp
            <div class="repeat-row choice-row" data-choice-wrapper>
                <select name="{{ $field }}[{{ $index }}]" data-choice-select>
                    <option value="">Geen keuze</option>
                    @foreach ($options as $option)
                        <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                    @endforeach
                    <option value="__other" @selected(! $hasValueInOptions)>Andere</option>
                </select>
                <input
                    name="{{ $otherField }}[{{ $index }}]"
                    value="{{ ! $hasValueInOptions ? $value : '' }}"
                    placeholder="Andere waarde"
                    data-choice-other
                    @if ($hasValueInOptions) hidden @endif
                >
                <button type="button" class="repeat-remove">Verwijder</button>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" data-add-repeat="{{ $field }}">+ {{ $label }}</button>
</div>

@props([
    'label',
    'count' => null,
    'color' => '#6b7280',
    'active' => false,
    'href' => null,
    'name' => null,
    'value' => null,
    'dataCat' => null,
    'dataSearch' => null,
])

@php
    $classes = trim('map-filter-btn ' . ($active ? 'active' : ''));
    $style = '--cat-color:' . $color;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'style' => $style, 'data-zoekterm' => $dataSearch]) }}>
        <span class="accordionItem-label">{{ $label }}</span>
        @if ($count !== null)
            <span class="accordionItem-count">{{ $count }}</span>
        @endif
    </a>
@elseif ($name)
    <label {{ $attributes->merge(['class' => $classes, 'style' => $style, 'data-zoekterm' => $dataSearch]) }}>
        <input type="checkbox" name="{{ $name }}" value="{{ $value ?? $label }}" @checked($active) onchange="this.form.submit()">
        <span class="accordionItem-label">{{ $label }}</span>
        @if ($count !== null)
            <span class="accordionItem-count">{{ $count }}</span>
        @endif
    </label>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes, 'style' => $style, 'data-cat' => $dataCat ?? $label]) }}>
        <span class="accordionItem-label">{{ $label }}</span>
        @if ($count !== null)
            <span class="accordionItem-count">{{ $count }}</span>
        @endif
    </button>
@endif

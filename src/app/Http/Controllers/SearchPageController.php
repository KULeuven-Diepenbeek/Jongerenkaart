<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\OrganisatieFilterOptionsService;
use App\Services\OrganisatieLocationService;
use Illuminate\Contracts\View\View;

class SearchPageController extends Controller
{
    private const EXTRA_FILTER_LABELS = [
        'leeftijdscategorie' => 'Leeftijd',
        'rubriek' => 'Rubriek',
        'werkingsgebieden' => 'Werkingsgebied',
        'taalAanbod' => 'Taal',
        'betalingswijze' => 'Betaling',
        'aanbod' => 'Aanbod',
        'anoniem' => 'Anoniem',
    ];

    public function __invoke(OrganisatieFilterOptionsService $filterOptions, OrganisatieLocationService $locations): View
    {
        $categoryColors = config('jongerenkaart.category_colors', []);
        $options = $filterOptions->get();

        return view('pages.search', [
            'categories' => collect($options['categorie'] ?? [])
                ->map(fn (string $category) => [
                    'label' => $category,
                    'color' => $categoryColors[$category] ?? '#6b7280',
                ])
                ->values(),
            'extraFilters' => collect(self::EXTRA_FILTER_LABELS)
                ->map(fn (string $label, string $field) => $this->filterConfig($field, $label, $options[$field] ?? []))
                ->filter(fn (array $filter) => $filter['options'] !== [])
                ->values(),
            'places' => $locations->placeOptions(),
            'radiusOptions' => $locations->radiusOptions(),
            'categoryColors' => $categoryColors,
            'reactiveSearchEnabled' => SiteSetting::reactiveSearchEnabled(),
        ]);
    }

    private function filterConfig(string $field, string $label, array $options): array
    {
        if ($field === 'anoniem') {
            return [
                'field' => 'anoniem',
                'label' => $label,
                'type' => 'toggle',
                'options' => [[
                    'label' => 'Enkel anonieme hulp',
                    'value' => 'true',
                    'field' => 'anoniem',
                ]],
            ];
        }

        if ($field === 'leeftijdscategorie') {
            return [
                'field' => 'leeftijd',
                'label' => $label,
                'type' => 'number',
                'options' => [['label' => $label, 'value' => '', 'field' => 'leeftijd']],
            ];
        }

        $filterOptions = collect($options)
            ->map(fn (string $option) => [
                'label' => $option,
                'value' => $option,
                'field' => $field,
            ]);

        if ($field === 'betalingswijze') {
            $filterOptions->prepend([
                'label' => 'Gratis',
                'value' => 'true',
                'field' => 'isFree',
            ]);
        }

        return [
            'field' => $field,
            'label' => $label,
            'type' => 'select',
            'options' => $filterOptions->values()->all(),
        ];
    }
}

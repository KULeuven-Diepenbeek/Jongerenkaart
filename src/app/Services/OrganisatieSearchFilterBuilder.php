<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrganisatieSearchFilterBuilder
{
    public function build(Request $request): array
    {
        return collect($this->filterAliases())
            ->map(function (string $field, string $queryKey) use ($request) {
                $values = collect((array) $request->query($queryKey))
                    ->flatMap(fn (string $value) => preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn (string $value) => trim($value))
                    ->filter()
                    ->unique()
                    ->values();

                if ($values->isEmpty()) {
                    return null;
                }

                if (in_array($field, ['isFree', 'isAnonymous'], true)) {
                    return $this->booleanFilter($field, $values);
                }

                if ($field === 'ages') {
                    return $this->ageFilter($field, $values);
                }

                $quotedValues = $values
                    ->map(fn (string $value) => '"'.str_replace(['\\', '"'], ['\\\\', '\"'], $value).'"')
                    ->implode(', ');

                return $values->count() === 1
                    ? $field.' = '.$quotedValues
                    : $field.' IN ['.$quotedValues.']';
            })
            ->filter()
            ->values()
            ->all();
    }

    private function filterAliases(): array
    {
        return [
            'categorie' => 'category',
            'category' => 'category',
            'leeftijdscategorie' => 'leeftijdscategorie',
            'leeftijd' => 'ages',
            'age' => 'ages',
            'ages' => 'ages',
            'rubriek' => 'rubriek',
            'werkingsgebieden' => 'werkingsgebieden',
            'taalaanbod' => 'taalAanbod',
            'taalAanbod' => 'taalAanbod',
            'betalingswijze' => 'betalingswijze',
            'isFree' => 'isFree',
            'anoniem' => 'isAnonymous',
            'isAnonymous' => 'isAnonymous',
            'aanbod' => 'aanbod',
        ];
    }

    private function ageFilter(string $field, Collection $values): ?string
    {
        $ages = $values
            ->map(fn (string $value) => filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 120]]))
            ->filter(fn (int|false $value) => $value !== false)
            ->unique()
            ->values();

        if ($ages->isEmpty()) {
            return null;
        }

        if ($ages->count() === 1) {
            return $field.' = '.$ages->first();
        }

        return $field.' IN ['.$ages->implode(', ').']';
    }

    private function booleanFilter(string $field, Collection $values): ?string
    {
        $booleans = $values
            ->map(fn (string $value) => match (strtolower($value)) {
                '1', 'true', 'ja', 'yes' => true,
                '0', 'false', 'nee', 'no' => false,
                default => null,
            })
            ->filter(fn (?bool $value) => $value !== null)
            ->unique()
            ->values();

        if ($booleans->count() !== 1) {
            return null;
        }

        return $field.' = '.($booleans->first() ? 'true' : 'false');
    }
}

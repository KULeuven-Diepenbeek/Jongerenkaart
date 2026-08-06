<?php

namespace App\Services;

use App\Models\Organisatie;
use App\Models\OrganisatieFilterOption;
use Illuminate\Support\Facades\Cache;

class OrganisatieFilterOptionsService
{
    public const CACHE_KEY = 'organisatie_filter_options_v3';

    public function get(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => $this->optionsFromStore());
    }

    public function rebuildFromOrganisations(): array
    {
        $counts = array_fill_keys($this->fields(), []);

        Organisatie::query()
            ->cursor()
            ->each(function (Organisatie $organisatie) use (&$counts): void {
                foreach ($this->extractValues($organisatie) as $field => $values) {
                    foreach ($values as $value) {
                        $counts[$field][$value] = ($counts[$field][$value] ?? 0) + 1;
                    }
                }
            });

        OrganisatieFilterOption::query()->delete();

        foreach ($counts as $field => $values) {
            foreach ($values as $value => $count) {
                OrganisatieFilterOption::query()->create([
                    'field' => $field,
                    'value' => $value,
                    'count' => $count,
                ]);
            }
        }

        return $this->refreshCacheFromStore();
    }

    public function syncOrganisationChange(Organisatie|array|null $before, Organisatie|array|null $after): array
    {
        $beforeValues = $this->normalizeSnapshot($before);
        $afterValues = $this->normalizeSnapshot($after);

        foreach ($this->fields() as $field) {
            $removed = array_diff($beforeValues[$field] ?? [], $afterValues[$field] ?? []);
            $added = array_diff($afterValues[$field] ?? [], $beforeValues[$field] ?? []);

            foreach ($removed as $value) {
                $this->adjustCount($field, $value, -1);
            }

            foreach ($added as $value) {
                $this->adjustCount($field, $value, 1);
            }
        }

        return $this->refreshCacheFromStore();
    }

    public function snapshot(Organisatie $organisatie): array
    {
        return $this->extractValues($organisatie);
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function fields(): array
    {
        return [
            'categorie',
            'subcategorie',
            'subsubcategorie',
            'leeftijdscategorie',
            'rubriek',
            'openingsuren',
            'werkingsgebieden',
            'taalAanbod',
            'betalingswijze',
            'aanbod',
            'erkendDoor',
            'erkendAls',
            'juridischeVorm',
            'werkingstalen',
            'wijken',
            'faciliteiten',
            'zorgverleners',
            'aanmeldingswijze',
        ];
    }

    private function optionsFromStore(): array
    {
        $options = array_fill_keys($this->fields(), []);

        OrganisatieFilterOption::query()
            ->where('count', '>', 0)
            ->get()
            ->each(function (OrganisatieFilterOption $option) use (&$options): void {
                $field = (string) $option->field;
                $value = trim((string) $option->value);

                if (array_key_exists($field, $options) && $value !== '') {
                    $options[$field][] = $value;
                }
            });

        foreach ($options as $field => $values) {
            $values = array_values(array_unique($values));
            natcasesort($values);
            $options[$field] = array_values($values);
        }

        return $options;
    }

    private function refreshCacheFromStore(): array
    {
        $options = $this->optionsFromStore();
        Cache::forever(self::CACHE_KEY, $options);

        return $options;
    }

    private function adjustCount(string $field, string $value, int $delta): void
    {
        $value = trim($value);

        if ($value === '') {
            return;
        }

        $option = OrganisatieFilterOption::query()
            ->where('field', $field)
            ->where('value', $value)
            ->first();

        if (! $option instanceof OrganisatieFilterOption) {
            if ($delta > 0) {
                OrganisatieFilterOption::query()->create([
                    'field' => $field,
                    'value' => $value,
                    'count' => $delta,
                ]);
            }

            return;
        }

        $nextCount = max(0, (int) $option->count + $delta);

        if ($nextCount === 0) {
            $option->delete();

            return;
        }

        $option->count = $nextCount;
        $option->save();
    }

    private function normalizeSnapshot(Organisatie|array|null $snapshot): array
    {
        if ($snapshot instanceof Organisatie) {
            return $this->extractValues($snapshot);
        }

        if (is_array($snapshot)) {
            return collect($this->fields())
                ->mapWithKeys(fn (string $field) => [$field => $this->uniqueValues($snapshot[$field] ?? [])])
                ->all();
        }

        return array_fill_keys($this->fields(), []);
    }

    private function extractValues(Organisatie $organisatie): array
    {
        return [
            'categorie' => $this->uniqueValues($organisatie->categorie),
            'subcategorie' => $this->uniqueValues($organisatie->subcategorie),
            'subsubcategorie' => $this->uniqueValues($organisatie->subsubcategorie),
            'leeftijdscategorie' => $this->uniqueValues($organisatie->leeftijdscategorie),
            'rubriek' => $this->uniqueValues($this->valuesFromItems($organisatie->rubrieken, ['naam', 'namen'])),
            'openingsuren' => $this->uniqueValues($this->valuesFromItems($organisatie->openingsuren, ['type'])),
            'werkingsgebieden' => $this->uniqueValues($organisatie->werkingsgebieden),
            'taalAanbod' => $this->uniqueValues($organisatie->taalAanbod),
            'betalingswijze' => $this->uniqueValues($organisatie->betalingswijze),
            'aanbod' => $this->uniqueValues($this->aanbodValues($organisatie->aanbod)),
            'erkendDoor' => $this->uniqueValues($this->valuesFromItems($organisatie->erkenningen, ['erkendDoor'])),
            'erkendAls' => $this->uniqueValues($this->valuesFromItems($organisatie->erkenningen, ['erkendAls'])),
            'juridischeVorm' => $this->uniqueValues(data_get($organisatie->administratieveGegevens, 'juridischeVorm')),
            'werkingstalen' => $this->uniqueValues(data_get($organisatie->administratieveGegevens, 'werkingstalen')),
            'wijken' => $this->uniqueValues($organisatie->wijken),
            'faciliteiten' => $this->uniqueValues($organisatie->faciliteiten),
            'zorgverleners' => $this->uniqueValues($this->aanbodValues($organisatie->zorgverleners)),
            'aanmeldingswijze' => $this->uniqueValues($organisatie->aanmeldingswijze),
        ];
    }

    private function valuesFromItems(mixed $items, array $keys): array
    {
        return collect($items)
            ->flatMap(function (mixed $item) use ($keys) {
                if (! is_array($item)) {
                    return [$item];
                }

                return collect($keys)
                    ->map(fn (string $key) => data_get($item, $key))
                    ->filter()
                    ->values();
            })
            ->all();
    }

    private function aanbodValues(mixed $aanbod): array
    {
        if (is_array($aanbod)) {
            return [
                data_get($aanbod, 'namen'),
                data_get($aanbod, 'naam'),
            ];
        }

        return [$aanbod];
    }

    private function uniqueValues(mixed $values): array
    {
        if ($values === null) {
            return [];
        }

        if (! is_array($values)) {
            $values = [$values];
        }

        return collect($values)
            ->flatMap(function (mixed $value) {
                if (is_array($value)) {
                    return $this->uniqueValues($value);
                }

                return explode(',', (string) $value);
            })
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter()
            ->unique(fn (string $value) => mb_strtolower($value))
            ->values()
            ->all();
    }
}

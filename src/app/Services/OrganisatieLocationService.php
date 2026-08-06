<?php

namespace App\Services;

use App\Models\Organisatie;
use Illuminate\Support\Collection;

class OrganisatieLocationService
{
    public function radiusOptions(): array
    {
        return [1, 3, ...range(5, 80, 5)];
    }

    public function placeOptions(): Collection
    {
        return $this->addressesWithCoordinates()
            ->filter(fn (array $address) => $this->placeName($address) !== null)
            ->groupBy(fn (array $address) => $this->placeKey($address))
            ->map(function (Collection $addresses, string $key) {
                return [
                    'key' => $key,
                    'label' => $this->placeLabel($addresses->first()),
                    'lat' => round($addresses->avg('lat'), 6),
                    'lon' => round($addresses->avg('lon'), 6),
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function placeByKey(string $key): ?array
    {
        return $this->placeOptions()->firstWhere('key', $key);
    }

    public function primaryAddressWithCoordinates(Organisatie $organisatie): ?array
    {
        $addresses = collect($organisatie->adresgegevens ?? [])
            ->filter(fn (mixed $address) => is_array($address))
            ->map(fn (array $address) => $this->addressWithCoordinates($address))
            ->filter()
            ->values();

        return $addresses->first(fn (array $address) => (bool) ($address['hoofd_adres'] ?? false))
            ?? $addresses->first();
    }

    public function distanceFromPlace(Organisatie $organisatie, array $place): ?float
    {
        $address = $this->primaryAddressWithCoordinates($organisatie);

        if (! $address) {
            return null;
        }

        return round($this->distanceInKm(
            (float) $place['lat'],
            (float) $place['lon'],
            (float) $address['lat'],
            (float) $address['lon'],
        ), 1);
    }

    public function filterWithinRadius(Collection $organisaties, ?array $place, int $radius): Collection
    {
        if (! $place) {
            return $organisaties;
        }

        return $organisaties
            ->map(function (Organisatie $organisatie) use ($place) {
                $organisatie->setAttribute('distance', $this->distanceFromPlace($organisatie, $place));

                return $organisatie;
            })
            ->filter(fn (Organisatie $organisatie) => $organisatie->getAttribute('distance') !== null && $organisatie->getAttribute('distance') <= $radius)
            ->sortBy(fn (Organisatie $organisatie) => $organisatie->getAttribute('distance'))
            ->values();
    }

    public function addressWithCoordinates(array $address): ?array
    {
        $lat = data_get($address, 'locatie.coordinates.1')
            ?? data_get($address, 'coordinates.1')
            ?? data_get($address, 'locatie.y')
            ?? data_get($address, 'y')
            ?? data_get($address, 'geocoordinatenX')
            ?? data_get($address, 'lat')
            ?? data_get($address, 'latitude');
        $lon = data_get($address, 'locatie.coordinates.0')
            ?? data_get($address, 'coordinates.0')
            ?? data_get($address, 'locatie.x')
            ?? data_get($address, 'x')
            ?? data_get($address, 'geocoordinatenY')
            ?? data_get($address, 'lon')
            ?? data_get($address, 'lng')
            ?? data_get($address, 'longitude');

        if (! is_numeric($lat) || ! is_numeric($lon)) {
            return null;
        }

        $lat = (float) $lat;
        $lon = (float) $lon;

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return null;
        }

        return array_merge($address, [
            'lat' => $lat,
            'lon' => $lon,
        ]);
    }

    private function addressesWithCoordinates(): Collection
    {
        return Organisatie::query()
            ->get()
            ->flatMap(fn (Organisatie $organisatie) => collect($organisatie->adresgegevens ?? []))
            ->filter(fn (mixed $address) => is_array($address))
            ->map(fn (array $address) => $this->addressWithCoordinates($address))
            ->filter()
            ->values();
    }

    private function placeKey(array $address): string
    {
        return strtolower(trim(($address['postcode'] ?? '').'|'.$this->placeName($address)));
    }

    private function placeLabel(array $address): string
    {
        $name = $this->placeName($address);
        $postcode = trim((string) ($address['postcode'] ?? ''));

        return $postcode !== '' ? "{$name} ({$postcode})" : (string) $name;
    }

    private function placeName(array $address): ?string
    {
        $name = trim((string) ($address['deelgemeente'] ?? $address['gemeente'] ?? ''));

        return $name !== '' ? $name : null;
    }

    private function distanceInKm(float $fromLat, float $fromLon, float $toLat, float $toLon): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($toLat - $fromLat);
        $lonDelta = deg2rad($toLon - $fromLon);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use App\Models\SiteSetting;
use App\Services\OrganisatieFilterOptionsService;
use App\Services\OrganisatieLocationService;
use App\Services\OrganisatieSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MapController extends Controller
{
    public function __construct(
        private readonly OrganisatieSearchService $searchService,
        private readonly OrganisatieLocationService $locations,
        private readonly OrganisatieFilterOptionsService $filterOptions,
    ) {
    }

    public function index(): View
    {
        $categoryColors = config('jongerenkaart.category_colors') ?? [];
        $categories = $this->filterOptions->get()['categorie'] ?? [];

        return view('pages.map', [
            'categories' => collect($categories)
                ->map(fn (string $label) => [
                    'label' => $label,
                    'color' => $categoryColors[$label] ?? '#6b7280',
                ])
                ->values(),
            'categoryColors' => $categoryColors,
            'places' => $this->locations->placeOptions(),
            'radiusOptions' => $this->locations->radiusOptions(),
            'reactiveSearchEnabled' => SiteSetting::reactiveSearchEnabled(),
        ]);
    }

    public function places(): JsonResponse
    {
        return response()->json($this->locations->placeOptions()->values());
    }

    public function results(Request $request): JsonResponse
    {
        $placeKey = (string) $request->query('place', '');
        $radius = min(max((int) $request->query('radius', 10), 1), 80);
        $categories = collect((array) $request->query('categorie', []))
            ->filter()
            ->values();
        $matchingIds = $this->matchingSearchIds($request);
        $place = $this->requestedPlace($request, $placeKey);

        if (! $place) {
            return response()->json([
                'place' => null,
                'radius' => $radius,
                'results' => [],
            ]);
        }

        $results = $this->organisationsWithLocations()
            ->filter(function (array $item) use ($categories, $matchingIds, $place, $radius) {
                if ($matchingIds !== null && ! $matchingIds->contains($item['id'])) {
                    return false;
                }

                if ($categories->isNotEmpty() && ! $categories->contains($item['category'])) {
                    return false;
                }

                return $this->distanceInKm(
                    (float) $place['lat'],
                    (float) $place['lon'],
                    (float) $item['lat'],
                    (float) $item['lon'],
                ) <= $radius;
            })
            ->map(function (array $item) use ($place) {
                $item['distance'] = round($this->distanceInKm(
                    (float) $place['lat'],
                    (float) $place['lon'],
                    (float) $item['lat'],
                    (float) $item['lon'],
                ), 1);

                return $item;
            })
            ->sortBy('distance')
            ->values();

        return response()->json([
            'place' => $place,
            'radius' => $radius,
            'results' => $results,
        ]);
    }

    private function matchingSearchIds(Request $request): ?Collection
    {
        if (trim((string) $request->query('q', '')) === '' && ! filled($request->query('anoniem'))) {
            return null;
        }

        return $this->searchService
            ->search($request, 100)
            ->map(fn (Organisatie $organisatie) => (string) $organisatie->getKey())
            ->values();
    }

    private function requestedPlace(Request $request, string $placeKey): ?array
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');

        if (is_numeric($lat) && is_numeric($lon)) {
            $lat = (float) $lat;
            $lon = (float) $lon;

            if ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                return [
                    'key' => 'custom',
                    'label' => (string) $request->query('label', 'Gekozen locatie'),
                    'lat' => $lat,
                    'lon' => $lon,
                ];
            }
        }

        return $this->locations->placeByKey($placeKey);
    }

    private function placeOptions(): Collection
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

    private function organisationsWithLocations(): Collection
    {
        return Organisatie::query()
            ->get()
            ->map(function (Organisatie $organisatie) {
                $address = $this->locations->primaryAddressWithCoordinates($organisatie);

                if (! $address) {
                    return null;
                }

                $addressText = $organisatie->formatAdres($address);

                return [
                    'id' => (string) $organisatie->getKey(),
                    'url' => route('organisaties.show', ['id' => (string) $organisatie->getKey()]),
                    'name' => $organisatie->display_name,
                    'category' => $organisatie->categorie,
                    'description' => $organisatie->algemeneWerking ?? '',
                    'address' => $addressText,
                    'addressUrl' => $organisatie->navigationUrlForAddress($address),
                    'email' => $organisatie->getPrimairEmail(),
                    'isFree' => $organisatie->isGratis(),
                    'isAnonymous' => $organisatie->isAnoniem(),
                    'lat' => $address['lat'],
                    'lon' => $address['lon'],
                    'distance' => null,
                ];
            })
            ->filter()
            ->values();
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

    private function primaryAddressWithCoordinates(Organisatie $organisatie): ?array
    {
        $addresses = collect($organisatie->adresgegevens ?? [])
            ->filter(fn (mixed $address) => is_array($address))
            ->map(fn (array $address) => $this->addressWithCoordinates($address))
            ->filter()
            ->values();

        return $addresses->first(fn (array $address) => (bool) ($address['hoofd_adres'] ?? false))
            ?? $addresses->first();
    }

    private function addressWithCoordinates(array $address): ?array
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

    private function placeKey(array $address): string
    {
        return strtolower(trim(($address['postcode'] ?? '').'|'.$this->placeName($address)));
    }

    private function placeLabel(array $address): string
    {
        return trim(implode(' ', array_filter([
            $address['postcode'] ?? null,
            $this->placeName($address),
        ])));
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

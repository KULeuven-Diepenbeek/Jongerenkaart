<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use App\Services\OrganisatieDeletionService;
use App\Services\OrganisatieFilterOptionsService;
use App\Services\OrganisatieLocationService;
use App\Services\OrganisatieSearchService;
use App\Services\TrafficRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Response;

class OrganisatieController extends Controller
{
    public function __construct(
        private readonly OrganisatieFilterOptionsService $filterOptions,
        private readonly OrganisatieSearchService $searchService,
        private readonly OrganisatieLocationService $locations,
        private readonly TrafficRecorder $traffic,
        private readonly OrganisatieDeletionService $deletionService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);
        $query = Organisatie::query()->orderBy('publiekeNaam');

        if (! $request->user()->isAdmin()) {
            $ids = collect($request->user()->organisatie_ids ?? [])
                ->map(fn (string $id) => $this->toObjectId($id))
                ->filter()
                ->values();

            $query->whereIn('_id', $ids);
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), Response::HTTP_FORBIDDEN);

        $organisatie = new Organisatie($this->organisatieData($request));
        $organisatie->save();
        $organisatie->refresh();
        $this->refreshSearchData(null, $organisatie);
        $this->traffic->recordOrganisationChange('created');

        return response()->json($this->serializeOrganisatie($organisatie), Response::HTTP_CREATED);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $organisatie = $this->findOrganisatie($id);
        $this->authorizeOrganisationAccess($request, $organisatie);

        return response()->json($this->serializeOrganisatie($organisatie));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $organisatie = $this->findOrganisatie($id);
        $this->authorizeOrganisationAccess($request, $organisatie);
        $before = $this->filterOptions->snapshot($organisatie);
        $organisatie->fill($this->organisatieData($request));
        $organisatie->save();
        $organisatie->refresh();
        $this->refreshSearchData($before, $organisatie);
        $this->traffic->recordOrganisationChange('updated');

        return response()->json($this->serializeOrganisatie($organisatie));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), Response::HTTP_FORBIDDEN);

        $this->deletionService->delete($this->findOrganisatie($id));

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function search(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->query('limit', 100), 1), 100);
        $place = $this->locations->placeByKey((string) $request->query('place', ''));
        $radius = min(max((int) $request->query('radius', 10), 1), 80);
        $hasSearchCriteria = $this->hasSearchCriteria($request);

        if (! $hasSearchCriteria && ! $place) {
            return response()->json([]);
        }

        $organisaties = $hasSearchCriteria
            ? $this->searchService->search($request, $limit)
            : Organisatie::query()->get();

        $organisaties = $this->locations->filterWithinRadius($organisaties, $place, $radius);

        return response()->json(
            $organisaties
                ->take($limit)
                ->map(fn (Organisatie $organisatie) => $this->serializeSearchResult($organisatie))
                ->values()
        );
    }

    public function filterOptions(): JsonResponse
    {
        return response()->json($this->filterOptions->get());
    }

    private function findOrganisatie(string $id): Organisatie
    {
        try {
            $objectId = new ObjectId($id);
        } catch (\Throwable) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $organisatie = Organisatie::query()->find($objectId);

        abort_unless($organisatie instanceof Organisatie, Response::HTTP_NOT_FOUND);

        return $organisatie;
    }

    private function authorizeOrganisationAccess(Request $request, Organisatie $organisatie): void
    {
        abort_unless(
            $request->user()->canManageOrganisatie((string) $organisatie->getKey()),
            Response::HTTP_FORBIDDEN,
        );
    }

    private function organisatieData(Request $request): array
    {
        return collect($request->only(Organisatie::KNOWN_FIELDS))
            ->reject(fn ($value) => $value === null)
            ->all();
    }

    private function refreshSearchData(?array $before, Organisatie $organisatie): void
    {
        $organisatie->searchable();
        $this->filterOptions->syncOrganisationChange($before, $organisatie);
    }

    private function serializeSearchResult(Organisatie $organisatie): array
    {
        $address = $this->primaryAddress($organisatie);

        return [
            'id' => (string) $organisatie->getKey(),
            'url' => route('organisaties.show', ['id' => (string) $organisatie->getKey()]),
            'name' => $organisatie->display_name,
            'category' => $organisatie->categorie,
            'subcategory' => $organisatie->subcategorie,
            'subsubcategory' => $organisatie->subsubcategorie,
            'description' => $organisatie->algemeneWerking ?? '',
            'address' => $address['label'] ?? null,
            'addressUrl' => $address['url'] ?? null,
            'email' => $this->contactEmail($organisatie),
            'isFree' => $organisatie->isGratis(),
            'isAnonymous' => $organisatie->isAnoniem(),
            'distance' => $organisatie->getAttribute('distance'),
        ];
    }

    private function hasSearchCriteria(Request $request): bool
    {
        return collect([
            'q',
            'categorie',
            'category',
            'leeftijd',
            'age',
            'ages',
            'rubriek',
            'werkingsgebieden',
            'taalaanbod',
            'taalAanbod',
            'betalingswijze',
            'isFree',
            'anoniem',
            'isAnonymous',
            'aanbod',
        ])->contains(fn (string $key) => filled($request->query($key)));
    }

    private function contactEmail(Organisatie $organisatie): ?string
    {
        $generalEmail = collect(data_get($organisatie->contactgegevens, 'emailadressen', []))
            ->filter(fn (mixed $email) => is_array($email))
            ->sortByDesc(fn (array $email) => (bool) ($email['primair'] ?? false))
            ->pluck('adres')
            ->map(fn (mixed $email) => trim((string) $email))
            ->first(fn (string $email) => $email !== '');

        if ($generalEmail) {
            return $generalEmail;
        }

        $contactPersonEmail = collect($organisatie->contactpersonen ?? [])
            ->filter(fn (mixed $contactPerson) => is_array($contactPerson))
            ->pluck('email')
            ->map(fn (mixed $email) => trim((string) $email))
            ->first(fn (string $email) => $email !== '');

        return $contactPersonEmail ?: null;
    }

    private function primaryAddress(Organisatie $organisatie): ?array
    {
        $addresses = collect($organisatie->adresgegevens ?? [])
            ->filter(fn (mixed $address) => is_array($address));

        $address = $addresses->first(fn (array $address) => (bool) ($address['hoofd_adres'] ?? false))
            ?? $addresses->first();

        if (! is_array($address)) {
            return null;
        }

        $street = trim(implode(' ', array_filter([
            $address['straatnaam'] ?? null,
            $address['huisnummer'] ?? null,
        ])));
        $city = trim(implode(' ', array_filter([
            $address['postcode'] ?? null,
            $address['deelgemeente'] ?? null,
        ])));
        $label = implode(', ', array_filter([$street, $city]));

        if ($label === '') {
            return null;
        }

        return [
            'label' => $label,
            'url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($label),
        ];
    }

    private function serializeOrganisatie(Organisatie $organisatie): array
    {
        return array_merge($organisatie->toArray(), [
            'id' => (string) $organisatie->getKey(),
        ]);
    }
}

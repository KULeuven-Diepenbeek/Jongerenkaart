<?php

namespace App\Services;

use App\Models\Organisatie;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Meilisearch\Client;
use MongoDB\BSON\ObjectId;
use Throwable;

class OrganisatieSearchService
{
    public function __construct(
        private readonly OrganisatieSearchFilterBuilder $filterBuilder,
    ) {}

    public function search(Request $request, int $limit = 5): Collection
    {
        $query = trim((string) $request->query('q', ''));
        $filters = $this->filterBuilder->build($request);

        if ($query === '' && $filters === []) {
            return collect();
        }

        $options = ['limit' => $limit];

        if ($filters !== []) {
            $options['filter'] = $filters;
        }

        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
        $hits = collect($client->index(Organisatie::COLLECTION)->search($query, $options)->getHits());
        $ids = $hits->pluck('id')->filter()->values();

        $objectIds = $ids
            ->map(fn (string $id) => $this->toObjectId($id))
            ->filter()
            ->values();

        $organisaties = Organisatie::query()
            ->whereIn('_id', $objectIds)
            ->get()
            ->keyBy(fn (Organisatie $organisatie) => (string) $organisatie->getKey());

        return $ids
            ->map(fn (string $id) => $organisaties->get($id))
            ->filter()
            ->values();
    }

    private function toObjectId(string $id): ?ObjectId
    {
        try {
            return new ObjectId($id);
        } catch (Throwable) {
            return null;
        }
    }
}

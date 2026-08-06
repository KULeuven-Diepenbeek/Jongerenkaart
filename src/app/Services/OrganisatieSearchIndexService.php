<?php

namespace App\Services;

use App\Models\Organisatie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

class OrganisatieSearchIndexService
{
    public function rebuild(): array
    {
        $this->deleteIndex();
        $this->ensureIndex();
        $this->syncSettings();
        $imported = $this->importDocuments();

        return [
            'Meilisearch instellingen zijn gesynchroniseerd.',
            'De oude organisatie-index is opnieuw aangemaakt.',
            $imported.' organisaties zijn opnieuw geimporteerd naar Meilisearch.',
        ];
    }

    private function deleteIndex(): void
    {
        $response = $this->request()
            ->delete($this->url('/indexes/'.Organisatie::COLLECTION));

        if ($response->status() === 404) {
            return;
        }

        $task = $response->throw()->json('taskUid');

        try {
            $this->waitForTask($task);
        } catch (\RuntimeException $exception) {
            if (! str_contains($exception->getMessage(), 'Index `'.Organisatie::COLLECTION.'` not found')) {
                throw $exception;
            }
        }
    }

    private function ensureIndex(): void
    {
        $response = $this->request()
            ->post($this->url('/indexes'), [
                'uid' => Organisatie::COLLECTION,
                'primaryKey' => 'id',
            ]);

        if ($response->failed() && $response->status() !== 400) {
            $response->throw();
        }

        $this->waitForTask($response->json('taskUid'));
        $this->waitForIndex();
    }

    private function syncSettings(): void
    {
        $settings = config('scout.meilisearch.index-settings.'.Organisatie::COLLECTION, []);

        if ($settings === []) {
            return;
        }

        $task = $this->request()
            ->patch($this->url('/indexes/'.Organisatie::COLLECTION.'/settings'), $settings)
            ->throw()
            ->json('taskUid');

        $this->waitForTask($task);
    }

    private function importDocuments(): int
    {
        $count = 0;

        Organisatie::query()
            ->cursor()
            ->chunk(100)
            ->each(function ($organisaties) use (&$count): void {
                $documents = $organisaties
                    ->map(fn (Organisatie $organisatie) => $organisatie->toSearchableArray())
                    ->values()
                    ->all();

                if ($documents === []) {
                    return;
                }

                $task = $this->request()
                    ->post($this->url('/indexes/'.Organisatie::COLLECTION.'/documents?primaryKey=id'), $documents)
                    ->throw()
                    ->json('taskUid');

                $this->waitForTask($task);
                $count += count($documents);
            });

        return $count;
    }

    private function request(int $timeout = 30)
    {
        $request = Http::timeout($timeout);
        $key = config('scout.meilisearch.key');

        return $key ? $request->withToken($key) : $request;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('scout.meilisearch.host'), '/').$path;
    }

    private function waitForTask(mixed $taskUid): void
    {
        if ($taskUid === null) {
            return;
        }

        $deadline = now()->addSeconds(60);

        do {
            $task = $this->request(timeout: 10)
                ->get($this->url('/tasks/'.$taskUid))
                ->throw()
                ->json();

            if (($task['status'] ?? null) === 'succeeded') {
                return;
            }

            if (($task['status'] ?? null) === 'failed') {
                throw new \RuntimeException(data_get($task, 'error.message', 'Meilisearch taak is mislukt.'));
            }

            Sleep::for(250)->milliseconds();
        } while (now()->lessThan($deadline));

        throw new \RuntimeException('Meilisearch taak duurt te lang. Probeer later opnieuw of controleer de Meilisearch logs.');
    }

    private function waitForIndex(): void
    {
        $deadline = now()->addSeconds(30);

        do {
            $response = $this->request(timeout: 5)
                ->get($this->url('/indexes/'.Organisatie::COLLECTION));

            if ($response->successful()) {
                return;
            }

            Sleep::for(250)->milliseconds();
        } while (now()->lessThan($deadline));

        throw new \RuntimeException('Meilisearch index kon niet worden aangemaakt.');
    }
}

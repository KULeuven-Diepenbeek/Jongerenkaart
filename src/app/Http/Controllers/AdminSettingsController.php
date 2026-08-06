<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\Medewerker;
use App\Models\Organisatie;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\OrganisatieFilterOptionsService;
use App\Services\OrganisatieSearchIndexService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSettingsController extends Controller
{
    public function __construct(
        private readonly OrganisatieFilterOptionsService $filterOptions,
        private readonly OrganisatieSearchIndexService $searchIndex,
    ) {}

    public function index(Request $request): View
    {
        Medewerker::seedDefaultsIfEmpty();
        $userQuery = trim((string) $request->query('user_q', ''));

        return view('admin.settings', [
            'status' => session('settings_status'),
            'medewerkers' => Medewerker::query()->orderBy('regio')->orderBy('naam')->get(),
            'users' => $this->users($userQuery),
            'userQuery' => $userQuery,
            'organisationMailCount' => count($this->organisationEmails()),
            'editablePages' => ContentPage::editablePages(),
            'customPageSlugs' => ContentPage::query()->pluck('slug')->all(),
            'reactiveSearchEnabled' => SiteSetting::reactiveSearchEnabled(),
            'contactEmail' => SiteSetting::contactEmail(),
        ]);
    }

    public function updateContactEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_email' => ['required', 'email:rfc', 'max:254'],
        ]);

        SiteSetting::setContactEmail($validated['contact_email']);

        return back()->with('settings_status', [
            'title' => 'Contactadres bijgewerkt',
            'items' => ['Berichten via de contactpagina worden voortaan gericht aan '.SiteSetting::contactEmail().'.'],
        ]);
    }

    public function updateSearchBehaviour(Request $request): RedirectResponse
    {
        $enabled = $request->boolean('reactive_search_enabled');
        SiteSetting::setReactiveSearchEnabled($enabled);

        return back()->with('settings_status', [
            'title' => 'Zoekgedrag bijgewerkt',
            'items' => [
                $enabled
                    ? 'Reactief zoeken is ingeschakeld. Zoekopdrachten starten tijdens het typen.'
                    : 'Reactief zoeken is uitgeschakeld. Zoekopdrachten starten pas na Enter.',
            ],
        ]);
    }

    public function check(): RedirectResponse
    {
        try {
            return back()->with('settings_status', $this->databaseStatus() + $this->cacheStatus() + $this->searchStatus());
        } catch (\Throwable $exception) {
            return back()->with('settings_status', $this->errorStatus($exception));
        }
    }

    public function rebuildCache(): RedirectResponse
    {
        try {
            $options = $this->filterOptions->rebuildFromOrganisations();

            return back()->with('settings_status', [
                'title' => 'Dropdown opties opnieuw opgebouwd',
                'items' => $this->optionCounts($options),
            ]);
        } catch (\Throwable $exception) {
            return back()->with('settings_status', $this->errorStatus($exception));
        }
    }

    public function rebuildSearch(): RedirectResponse
    {
        try {
            return back()->with('settings_status', [
                'title' => 'Zoekindex vernieuwd',
                'items' => $this->searchIndex->rebuild(),
            ]);
        } catch (\Throwable $exception) {
            return back()->with('settings_status', $this->errorStatus($exception));
        }
    }

    public function refreshAll(): RedirectResponse
    {
        try {
            $options = $this->filterOptions->rebuildFromOrganisations();

            return back()->with('settings_status', [
                'title' => 'Controle en vernieuwing uitgevoerd',
                'items' => array_merge(
                    $this->databaseStatus()['items'],
                    $this->optionCounts($options),
                    $this->searchIndex->rebuild(),
                ),
            ]);
        } catch (\Throwable $exception) {
            return back()->with('settings_status', $this->errorStatus($exception));
        }
    }

    public function exportBackup(): StreamedResponse
    {
        $filename = 'jongerenkaart-mongodb-organisaties-'.now()->format('Ymd-His').'.json';

        return response()->streamDownload(function (): void {
            echo '[';
            $first = true;

            Organisatie::query()
                ->cursor()
                ->each(function (Organisatie $organisatie) use (&$first): void {
                    if (! $first) {
                        echo ',';
                    }

                    echo PHP_EOL.json_encode($organisatie->getAttributes(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
                    $first = false;

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }

                    flush();
                });

            echo PHP_EOL.']';
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function mailOrganisaties(): RedirectResponse
    {
        $emails = $this->organisationEmails();
        $mailto = 'mailto:?subject='.rawurlencode('Jongerenkaart:').'&bcc='.rawurlencode(implode(',', $emails));

        return redirect()->away($mailto);
    }

    private function databaseStatus(): array
    {
        return [
            'title' => 'Systeemcontrole uitgevoerd',
            'items' => [
                'MongoDB organisaties: '.Organisatie::query()->count(),
                'Gebruikers: '.User::query()->count(),
            ],
        ];
    }

    private function users(string $query)
    {
        return User::query()
            ->get()
            ->when($query !== '', fn ($users) => $users->filter(
                fn (User $user) => str_contains(str($user->name ?? '')->lower()->toString(), str($query)->lower()->toString())
                    || str_contains(str($user->email ?? '')->lower()->toString(), str($query)->lower()->toString())
                    || str_contains(str($user->role ?? '')->lower()->toString(), str($query)->lower()->toString())
            ))
            ->sortBy(
                fn (User $user) => ($user->isActive() ? '1|' : '0|').strtolower((string) $user->email),
                SORT_NATURAL | SORT_FLAG_CASE,
            )
            ->values();
    }

    private function organisationEmails(): array
    {
        return Organisatie::query()
            ->get()
            ->flatMap(fn (Organisatie $organisatie) => $organisatie->contactEmailAddresses())
            ->map(fn (string $email) => strtolower(trim($email)))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function cacheStatus(): array
    {
        $options = Cache::get(OrganisatieFilterOptionsService::CACHE_KEY);

        return [
            'cache_items' => [
                'Dropdown cache: '.(is_array($options) ? 'aanwezig' : 'niet opgebouwd'),
                ...($options ? $this->optionCounts($options) : []),
            ],
        ];
    }

    private function searchStatus(): array
    {
        try {
            $health = $this->meilisearch(timeout: 3)->get($this->meilisearchUrl('/health'));
            $stats = $this->meilisearch(timeout: 3)->get($this->meilisearchUrl('/indexes/'.Organisatie::COLLECTION.'/stats'));

            return [
                'search_items' => [
                    'Meilisearch: '.($health->successful() ? 'bereikbaar' : 'niet bereikbaar'),
                    'Zoekindex documenten: '.($stats->successful() ? $stats->json('numberOfDocuments', 'onbekend') : 'onbekend'),
                ],
            ];
        } catch (\Throwable $exception) {
            return [
                'search_items' => [
                    'Meilisearch: niet bereikbaar',
                    'Fout: '.$exception->getMessage(),
                ],
            ];
        }
    }

    private function meilisearch(int $timeout = 30)
    {
        $request = Http::timeout($timeout);
        $key = config('scout.meilisearch.key');

        return $key ? $request->withToken($key) : $request;
    }

    private function meilisearchUrl(string $path): string
    {
        return rtrim((string) config('scout.meilisearch.host'), '/').$path;
    }

    private function optionCounts(array $options): array
    {
        return collect($options)
            ->map(fn (array $values, string $field) => $field.': '.count($values).' opties')
            ->values()
            ->all();
    }

    private function errorStatus(\Throwable $exception): array
    {
        return [
            'type' => 'error',
            'title' => 'Actie mislukt',
            'items' => [$exception->getMessage()],
        ];
    }
}

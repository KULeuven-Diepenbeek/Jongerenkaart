<?php

namespace App\Http\Controllers;

use App\Models\Organisatie;
use App\Services\OrganisatieDeletionService;
use App\Services\OrganisatieFilterOptionsService;
use App\Services\TrafficRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Response;

class OrganisationManagementController extends Controller
{
    private const OTHER_VALUE = '__other';

    public function __construct(
        private readonly OrganisatieFilterOptionsService $filterOptions,
        private readonly TrafficRecorder $traffic,
        private readonly OrganisatieDeletionService $deletionService,
    ) {}

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $organisaties = $this->manageableOrganisaties($request)
            ->when($query !== '', fn ($items) => $items->filter(
                fn (Organisatie $organisatie) => str_contains(
                    str($organisatie->publiekeNaam ?? '')->lower()->toString(),
                    str($query)->lower()->toString(),
                )
            ))
            ->sortBy('publiekeNaam', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('manage.organisations.index', [
            'organisaties' => $organisaties,
            'query' => $query,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), Response::HTTP_FORBIDDEN);

        return view('manage.organisations.form', [
            'organisatie' => new Organisatie([
                'adresgegevens' => [[
                    'hoofd_adres' => true,
                    'adrestype' => 'Hoofdadres',
                ]],
                'contactpersonen' => [[]],
            ]),
            'mode' => 'create',
            'options' => $this->filterOptions->get(),
            'action' => route('manage.organisations.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), Response::HTTP_FORBIDDEN);

        $data = $this->validatedOrganisationData($request, true);
        $organisatie = new Organisatie($data);
        $organisatie->save();
        $organisatie->refresh();
        $this->refreshSearchData(null, $organisatie);
        $this->traffic->recordOrganisationChange('created');

        return redirect()
            ->route('manage.organisations.edit', ['organisatie' => (string) $organisatie->getKey()])
            ->with('status', 'Organisatie aangemaakt.');
    }

    public function edit(Request $request, string $organisatie): View
    {
        $organisatie = $this->findManageableOrganisatie($request, $organisatie);

        return view('manage.organisations.form', [
            'organisatie' => $organisatie,
            'mode' => 'edit',
            'options' => $this->filterOptions->get(),
            'action' => route('manage.organisations.update', ['organisatie' => (string) $organisatie->getKey()]),
        ]);
    }

    public function update(Request $request, string $organisatie): RedirectResponse
    {
        $organisatie = $this->findManageableOrganisatie($request, $organisatie);
        $before = $this->filterOptions->snapshot($organisatie);
        $organisatie->fill($this->validatedOrganisationData($request, false));
        $organisatie->save();
        $organisatie->refresh();
        $this->refreshSearchData($before, $organisatie);
        $this->traffic->recordOrganisationChange('updated');

        return back()->with('status', 'Organisatie opgeslagen.');
    }

    public function destroy(Request $request, string $organisatie): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), Response::HTTP_FORBIDDEN);

        $organisatie = $this->findManageableOrganisatie($request, $organisatie);
        $name = $organisatie->display_name;
        $this->deletionService->delete($organisatie);

        return redirect()
            ->route('manage.organisations.index')
            ->with('status', $name.' is verwijderd.');
    }

    private function manageableOrganisaties(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return Organisatie::query()->get();
        }

        $ids = collect($user->organisatie_ids ?? [])
            ->map(fn (string $id) => $this->toObjectId($id))
            ->filter()
            ->values();

        $linkedOrganisaties = $ids->isNotEmpty()
            ? Organisatie::query()->whereIn('_id', $ids)->get()
            : collect();
        $emailOrganisaties = Organisatie::query()
            ->get()
            ->filter(fn (Organisatie $organisatie) => $organisatie->hasContactEmail($user->email));

        return $linkedOrganisaties
            ->merge($emailOrganisaties)
            ->unique(fn (Organisatie $organisatie) => (string) $organisatie->getKey())
            ->values();
    }

    private function findManageableOrganisatie(Request $request, string $id): Organisatie
    {
        $objectId = $this->toObjectId($id);
        abort_unless($objectId, Response::HTTP_NOT_FOUND);

        $organisatie = Organisatie::query()->find($objectId);
        abort_unless($organisatie instanceof Organisatie, Response::HTTP_NOT_FOUND);
        abort_unless($this->userCanManageOrganisatie($request, $organisatie), Response::HTTP_FORBIDDEN);

        return $organisatie;
    }

    private function userCanManageOrganisatie(Request $request, Organisatie $organisatie): bool
    {
        $user = $request->user();

        return $user->canManageOrganisatie((string) $organisatie->getKey())
            || $organisatie->hasContactEmail($user->email);
    }

    private function validatedOrganisationData(Request $request, bool $creating): array
    {
        $request->validate([
            'publiekeNaam' => ['required', 'string', 'max:255'],
            'algemeneWerking' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'contact_email' => ['nullable', 'email'],
            'contactpersonen' => ['nullable', 'array'],
            'contactpersonen.0.email' => ['nullable', 'email'],
        ]);

        $address = $this->cleanAddress((array) $request->input('adres', []));
        $contactEmail = trim((string) $request->input('contact_email', ''));
        $contactPhone = trim((string) $request->input('contact_phone', ''));
        $website = trim((string) $request->input('website', ''));

        if ($creating && $contactEmail === '' && trim((string) data_get($request->input('contactpersonen', []), '0.email', '')) === '') {
            throw ValidationException::withMessages([
                'contact_email' => 'Voeg een algemeen e-mailadres of minstens 1 contactpersoon met e-mailadres toe.',
            ]);
        }

        return [
            'publiekeNaam' => trim((string) $request->input('publiekeNaam')),
            'categorie' => $this->choiceValue($request, 'categorie'),
            'subcategorie' => $this->choiceValue($request, 'subcategorie'),
            'subsubcategorie' => $this->choiceValue($request, 'subsubcategorie'),
            'leeftijdscategorie' => $this->choiceValue($request, 'leeftijdscategorie'),
            'algemeneWerking' => (string) $request->input('algemeneWerking', ''),
            'werkingsgebieden' => $this->choiceList($request, 'werkingsgebieden'),
            'taalAanbod' => $this->choiceList($request, 'taalAanbod'),
            'betalingswijze' => $this->choiceValue($request, 'betalingswijze'),
            Organisatie::FIELD_KOSTPRIJS => trim((string) $request->input('kostprijs', '')),
            Organisatie::FIELD_ANONIEM => trim((string) $request->input('anoniem', '')),
            Organisatie::FIELD_BESCHIKBAARHEID_TELEFOON => trim((string) $request->input('beschikbaarheid_telefoon', '')),
            Organisatie::FIELD_BESCHIKBAARHEID_CHAT => trim((string) $request->input('beschikbaarheid_chat', '')),
            Organisatie::FIELD_SOCIALE_KAART_ID => trim((string) $request->input('sociale_kaart_id', '')),
            'rubrieken' => $this->nameList($request, 'rubrieken'),
            'aanbod' => ['namen' => implode(', ', $this->choiceList($request, 'aanbod'))],
            'erkenningen' => $this->cleanRecognitions((array) $request->input('erkenningen', [])),
            'administratieveGegevens' => [
                'namen' => $this->nestedChoiceList($request, 'administratieveGegevens.namen'),
                'ondernemingsnummers' => $this->nestedChoiceList($request, 'administratieveGegevens.ondernemingsnummers'),
                'juridischeVorm' => $this->nestedChoiceList($request, 'administratieveGegevens.juridischeVorm'),
                'werkingstalen' => $this->nestedChoiceList($request, 'administratieveGegevens.werkingstalen'),
            ],
            'aanmeldingswijze' => $this->choiceValue($request, 'aanmeldingswijze'),
            'wijken' => implode(', ', $this->choiceList($request, 'wijken')),
            'faciliteiten' => $this->choiceList($request, 'faciliteiten'),
            'zorgverleners' => ['naam' => implode(', ', $this->choiceList($request, 'zorgverleners'))],
            'openingsuren' => $this->cleanOpeningHours((array) $request->input('openingsuren', [])),
            'adresgegevens' => $address ? [$address] : [],
            'contactgegevens' => [
                'emailadressen' => $contactEmail !== '' ? [['adres' => $contactEmail, 'primair' => true]] : [],
                'telefoonnummers' => $contactPhone !== '' ? [['nummer' => $contactPhone, 'primair' => true]] : [],
                'online' => $website !== '' ? [['url' => $website, 'primair' => true]] : [],
            ],
            'contactpersonen' => $this->cleanContactPersons((array) $request->input('contactpersonen', [])),
            'url' => $website,
            'datumUpdateGegevens' => now()->toDateString(),
        ];
    }

    private function choiceValue(Request $request, string $field): ?string
    {
        $value = trim((string) $request->input($field));

        if ($value === self::OTHER_VALUE) {
            $value = trim((string) $request->input($field.'_other'));
        }

        return $value !== '' ? $value : null;
    }

    private function choiceList(Request $request, string $field): array
    {
        return collect((array) $request->input($field, []))
            ->map(function (mixed $value, int|string $index) use ($request, $field) {
                $value = trim((string) $value);

                if ($value === self::OTHER_VALUE) {
                    $value = trim((string) data_get($request->input($field.'_other', []), $index, ''));
                }

                return $value;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function nameList(Request $request, string $field): array
    {
        return collect($this->choiceList($request, $field))
            ->map(fn (string $value) => ['naam' => $value])
            ->values()
            ->all();
    }

    private function nestedChoiceList(Request $request, string $field): array
    {
        $fieldName = str_replace('.', '_', $field);

        return collect((array) data_get($request->input(), $field, []))
            ->map(function (mixed $value, int|string $index) use ($request, $fieldName) {
                $value = trim((string) $value);

                if ($value === self::OTHER_VALUE) {
                    $value = trim((string) data_get($request->input($fieldName.'_other', []), $index, ''));
                }

                return $value;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function cleanAddress(array $address): array
    {
        $lat = trim((string) ($address['geocoordinatenX'] ?? ''));
        $lon = trim((string) ($address['geocoordinatenY'] ?? ''));

        return array_filter([
            'hoofd_adres' => true,
            'adrestype' => trim((string) ($address['adrestype'] ?? 'Hoofdadres')),
            'postcode' => trim((string) ($address['postcode'] ?? '')),
            'deelgemeente' => trim((string) ($address['deelgemeente'] ?? '')),
            'straatnaam' => trim((string) ($address['straatnaam'] ?? '')),
            'huisnummer' => trim((string) ($address['huisnummer'] ?? '')),
            'busnummer' => trim((string) ($address['busnummer'] ?? '')),
            'geocoordinatenX' => is_numeric($lat) ? (float) $lat : null,
            'geocoordinatenY' => is_numeric($lon) ? (float) $lon : null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function cleanOpeningHours(array $rows): array
    {
        return collect($rows)
            ->filter(fn (mixed $row) => is_array($row))
            ->map(function (array $row) {
                $type = trim((string) ($row['type'] ?? ''));

                if ($type === self::OTHER_VALUE) {
                    $type = trim((string) ($row['type_other'] ?? ''));
                }

                return array_filter([
                    'type' => $type,
                    'dag' => trim((string) ($row['dag'] ?? '')),
                    'beginuur' => trim((string) ($row['beginuur'] ?? '')),
                    'einduur' => trim((string) ($row['einduur'] ?? '')),
                ], fn ($value) => $value !== '');
            })
            ->filter()
            ->values()
            ->all();
    }

    private function cleanRecognitions(array $rows): array
    {
        return collect($rows)
            ->filter(fn (mixed $row) => is_array($row))
            ->map(function (array $row) {
                $erkendDoor = trim((string) ($row['erkendDoor'] ?? ''));
                $erkendAls = trim((string) ($row['erkendAls'] ?? ''));

                if ($erkendDoor === self::OTHER_VALUE) {
                    $erkendDoor = trim((string) ($row['erkendDoor_other'] ?? ''));
                }

                if ($erkendAls === self::OTHER_VALUE) {
                    $erkendAls = trim((string) ($row['erkendAls_other'] ?? ''));
                }

                return array_filter([
                    'erkendDoor' => $erkendDoor,
                    'erkendAls' => $erkendAls,
                    'hcoNummer' => trim((string) ($row['hcoNummer'] ?? '')),
                    'capaciteitsnummer' => trim((string) ($row['capaciteitsnummer'] ?? '')),
                ], fn ($value) => $value !== '');
            })
            ->filter()
            ->values()
            ->all();
    }

    private function cleanContactPersons(array $rows): array
    {
        return collect($rows)
            ->filter(fn (mixed $row) => is_array($row))
            ->map(fn (array $row) => array_filter([
                'voornaam' => trim((string) ($row['voornaam'] ?? '')),
                'naam' => trim((string) ($row['naam'] ?? '')),
                'rol' => trim((string) ($row['rol'] ?? '')),
                'email' => trim((string) ($row['email'] ?? '')),
                'telefoonnummer' => trim((string) ($row['telefoonnummer'] ?? '')),
            ], fn ($value) => $value !== ''))
            ->filter()
            ->values()
            ->all();
    }

    private function refreshSearchData(?array $before, Organisatie $organisatie): void
    {
        $organisatie->searchable();
        $this->filterOptions->syncOrganisationChange($before, $organisatie);
    }

    private function toObjectId(string $id): ?ObjectId
    {
        try {
            return new ObjectId($id);
        } catch (\Throwable) {
            return null;
        }
    }
}

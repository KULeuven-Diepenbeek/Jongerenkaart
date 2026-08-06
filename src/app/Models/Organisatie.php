<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string|null $categorie
 * @property string|null $subcategorie
 * @property string|null $subsubcategorie
 * @property string|null $leeftijdscategorie
 * @property string|null $publiekeNaam
 * @property string|null $algemeneWerking
 * @property string|null $algemeneWerking_EN
 * @property string|null $werkingsgebieden
 * @property string|null $taalAanbod
 * @property string|null $betalingswijze
 * @property string|null $aanmeldingswijze
 * @property string|null $wijken
 * @property string|null $url
 * @property string|null $openingsuren_opmerkingen
 * @property string|null $openingsuren_online
 * @property string|null $reservation_link
 * @property string|null $login_email
 * @property string|null $sociale_kaart_id
 * @property array|null $rubrieken
 * @property array|null $adresgegevens
 * @property array|null $contactgegevens
 * @property array|null $doelgroep
 * @property array|null $openingsuren
 * @property array|null $contactpersonen
 * @property array|null $administratieveGegevens
 * @property array|null $erkenningen
 * @property array|null $aanbod
 * @property array|null $zorgverleners
 * @property array|null $faciliteiten
 */
class Organisatie extends Model
{
    use Searchable;

    public const COLLECTION = 'organisaties';

    public const FIELD_ANONIEM = 'Anoniem?';
    public const FIELD_KOSTPRIJS = 'Kostprijs';
    public const FIELD_BESCHIKBAARHEID_TELEFOON = 'Beschikbaarheid tel';
    public const FIELD_BESCHIKBAARHEID_CHAT = 'Beschikbaarheid chat';
    public const FIELD_SOCIALE_KAART_ID = 'sociale_kaart_id';

    public const KNOWN_FIELDS = [
        'categorie',
        'subcategorie',
        'subsubcategorie',
        'leeftijdscategorie',
        'rubrieken',
        'publiekeNaam',
        'adresgegevens',
        'contactgegevens',
        'algemeneWerking',
        'algemeneWerking_EN',
        'werkingsgebieden',
        'doelgroep',
        'openingsuren',
        'openingsuren_opmerkingen',
        'openingsuren_online',
        'contactpersonen',
        'administratieveGegevens',
        'erkenningen',
        'taalAanbod',
        'betalingswijze',
        'aanmeldingswijze',
        'aanbod',
        'zorgverleners',
        'wijken',
        'url',
        'datumUpdateGegevens',
        self::FIELD_ANONIEM,
        self::FIELD_KOSTPRIJS,
        self::FIELD_BESCHIKBAARHEID_TELEFOON,
        self::FIELD_BESCHIKBAARHEID_CHAT,
        self::FIELD_SOCIALE_KAART_ID,
        'faciliteiten',
        'reservation_link',
        'login_email',
    ];

    protected $connection = 'mongodb';

    protected $collection = self::COLLECTION;

    protected $primaryKey = '_id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'rubrieken' => 'array',
        'adresgegevens' => 'array',
        'contactgegevens' => 'array',
        'doelgroep' => 'array',
        'openingsuren' => 'array',
        'contactpersonen' => 'array',
        'administratieveGegevens' => 'array',
        'erkenningen' => 'array',
        'aanbod' => 'array',
        'zorgverleners' => 'array',
        'faciliteiten' => 'array',
        'datumUpdateGegevens' => 'date:Y-m-d',
    ];

    protected static function booted(): void
    {
        static::creating(function (Organisatie $organisatie) {
            if (! $organisatie->getKey()) {
                $organisatie->setAttribute($organisatie->getKeyName(), new ObjectId());
            }
        });
    }

    public function searchableAs(): string
    {
        return self::COLLECTION;
    }

    public function getScoutKey(): string
    {
        return (string) $this->getKey();
    }

    public function getScoutKeyName(): string
    {
        return 'id';
    }

    public function toSearchableArray(): array
    {
        $name = $this->display_name ?? '';
        $category = $this->categorie ?? '';
        $subcategory = $this->subcategorie ?? '';
        $subsubcategory = $this->subsubcategorie ?? '';
        $description = $this->algemeneWerking ?? '';
        $ageCategories = $this->normalizeFilterValue([
            $this->leeftijdscategorie,
            data_get($this->administratieveGegevens, 'leeftijdscategorieen'),
        ]);
        $contextParts = [
            $name,
            $category,
            $subcategory,
            $subsubcategory,
            $description,
        ];

        return [
            'id' => $this->getScoutKey(),
            'name' => $name,
            'category' => $category,
            'subcategory' => $subcategory,
            'subsubcategory' => $subsubcategory,
            'description' => $description,
            'context' => $this->normalizeSearchText($contextParts),
            'leeftijdscategorie' => $ageCategories,
            'ages' => $this->normalizeAges($ageCategories),
            'rubriek' => $this->normalizeFilterValue($this->rubrieken, ['naam', 'namen']),
            'openingsuren' => $this->normalizeFilterValue($this->openingsuren, ['type']),
            'werkingsgebieden' => $this->normalizeFilterValue($this->werkingsgebieden),
            'taalAanbod' => $this->normalizeFilterValue($this->taalAanbod),
            'betalingswijze' => $this->normalizeFilterValue($this->betalingswijze),
            'isFree' => $this->isGratis(),
            'isAnonymous' => $this->isAnoniem(),
            'aanbod' => $this->normalizeFilterValue($this->aanbod, ['naam', 'namen']),
        ];
    }

    public function isGratis(): bool
    {
        $attributes = $this->getAttributes();
        $costText = str((string) ($attributes[self::FIELD_KOSTPRIJS] ?? ''))->lower()->toString();

        return str_contains($costText, 'gratis') || str_contains($costText, 'kosteloos');
    }

    public function isAnoniem(): bool
    {
        $value = mb_strtolower(trim((string) ($this->getAttributes()[self::FIELD_ANONIEM] ?? '')));

        return preg_match('/^ja\b/u', $value) === 1;
    }

    private function normalizeAges(mixed $value): array
    {
        return collect($this->flattenToStrings($value))
            ->flatMap(fn (string $ageCategory) => $this->agesFromCategory($ageCategory))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function agesFromCategory(string $ageCategory): array
    {
        $ages = [];

        if (preg_match_all('/(\d+)\s*[-–]\s*(\d+)/', $ageCategory, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $from = max(0, (int) $match[1]);
                $to = min(120, (int) $match[2]);

                if ($from <= $to) {
                    array_push($ages, ...range($from, $to));
                }
            }
        }

        if (preg_match_all('/(\d+)\s*\+/', $ageCategory, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                array_push($ages, ...range(max(0, (int) $match[1]), 120));
            }
        }

        if (preg_match('/vanaf\s*(\d+)/i', $ageCategory, $match)) {
            array_push($ages, ...range(max(0, (int) $match[1]), 120));
        }

        if (preg_match('/tot\s*(\d+)/i', $ageCategory, $match)) {
            array_push($ages, ...range(0, min(120, (int) $match[1])));
        }

        return array_values(array_unique($ages));
    }

    private function normalizeSearchText(mixed $value): string
    {
        return implode(' ', $this->flattenToStrings($value));
    }

    private function normalizeFilterValue(mixed $value, array $preferredKeys = []): array|string|null
    {
        $values = $this->flattenToStrings($value, $preferredKeys);

        if ($values === []) {
            return null;
        }

        return count($values) === 1 ? $values[0] : $values;
    }

    private function flattenToStrings(mixed $value, array $preferredKeys = []): array
    {
        if ($value === null) {
            return [];
        }

        if (is_scalar($value)) {
            return $this->splitFilterString((string) $value);
        }

        if ($value instanceof \Stringable) {
            return $this->splitFilterString((string) $value);
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (! is_array($value)) {
            return [];
        }

        if ($preferredKeys !== [] && ! array_is_list($value)) {
            $preferredValues = [];

            foreach ($preferredKeys as $key) {
                if (array_key_exists($key, $value)) {
                    array_push($preferredValues, ...$this->flattenToStrings($value[$key], $preferredKeys));
                }
            }

            return array_values(array_unique($preferredValues));
        }

        $values = [];

        foreach ($value as $item) {
            array_push($values, ...$this->flattenToStrings($item, $preferredKeys));
        }

        return array_values(array_unique($values));
    }

    private function splitFilterString(string $value): array
    {
        return collect(preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getDisplayNameAttribute(): ?string
    {
        return $this->publiekeNaam;
    }

    public function getAnoniemAttribute(): ?string
    {
        $value = $this->getAttributes()[self::FIELD_ANONIEM] ?? null;

        return $value !== null ? (string) $value : null;
    }

    public function getIsAnoniemAttribute(): bool
    {
        return $this->isAnoniem();
    }

    public function getKostprijsAttribute(): ?string
    {
        $value = $this->getAttributes()[self::FIELD_KOSTPRIJS] ?? null;

        return $value !== null ? (string) $value : null;
    }

    public function getBeschikbaarheidTelefoonAttribute(): ?string
    {
        return $this->stringAttribute(self::FIELD_BESCHIKBAARHEID_TELEFOON);
    }

    public function getBeschikbaarheidChatAttribute(): ?string
    {
        return $this->stringAttribute(self::FIELD_BESCHIKBAARHEID_CHAT);
    }

    public function getSocialeKaartIdAttribute(): ?string
    {
        return $this->stringAttribute(self::FIELD_SOCIALE_KAART_ID);
    }

    private function stringAttribute(string $field): ?string
    {
        $value = $this->getAttributes()[$field] ?? null;

        return $value !== null ? (string) $value : null;
    }

    public function hoofdAdres(): ?array
    {
        $addresses = collect($this->adresgegevens ?? [])
            ->filter(fn (mixed $address) => is_array($address));

        return $addresses->first(fn (array $address) => (bool) ($address['hoofd_adres'] ?? false))
            ?? $addresses->first();
    }

    public function formatAdres(?array $address = null): ?string
    {
        if ($address === null) {
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
        $formatted = implode(', ', array_filter([$street, $city]));

        return $formatted !== '' ? $formatted : null;
    }

    public function navigationUrlForAddress(?array $address = null): ?string
    {
        $formatted = $this->formatAdres($address);

        return $formatted ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($formatted) : null;
    }

    public function adresMetCoordinaten(): ?array
    {
        $addresses = collect($this->adresgegevens ?? [])
            ->filter(fn (mixed $address) => is_array($address))
            ->filter(fn (array $address) => $this->latFromAddress($address) !== null && $this->lonFromAddress($address) !== null);

        return $addresses->first(fn (array $address) => (bool) ($address['hoofd_adres'] ?? false))
            ?? $addresses->first();
    }

    public function getPrimairTelefoon(): ?string
    {
        return collect(data_get($this->contactgegevens, 'telefoonnummers', []))
            ->filter(fn (mixed $phone) => is_array($phone))
            ->sortByDesc(fn (array $phone) => (bool) ($phone['primair'] ?? false))
            ->pluck('nummer')
            ->map(fn (mixed $phone) => trim((string) $phone))
            ->first(fn (string $phone) => $phone !== '');
    }

    public function getPrimairEmail(): ?string
    {
        return collect(data_get($this->contactgegevens, 'emailadressen', []))
            ->filter(fn (mixed $email) => is_array($email))
            ->sortByDesc(fn (array $email) => (bool) ($email['primair'] ?? false))
            ->pluck('adres')
            ->map(fn (mixed $email) => trim((string) $email))
            ->first(fn (string $email) => $email !== '');
    }

    public function contactEmailAddresses(): array
    {
        $generalEmails = collect(data_get($this->contactgegevens, 'emailadressen', []))
            ->filter(fn (mixed $item) => is_array($item))
            ->pluck('adres');
        $contactPersonEmails = collect($this->contactpersonen ?? [])
            ->filter(fn (mixed $item) => is_array($item))
            ->pluck('email');

        return $generalEmails
            ->merge($contactPersonEmails)
            ->map(fn (mixed $email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function hasContactEmail(string $email): bool
    {
        return in_array(strtolower(trim($email)), $this->contactEmailAddresses(), true);
    }

    public function getPrimairWebsite(): ?string
    {
        return collect(data_get($this->contactgegevens, 'online', []))
            ->filter(fn (mixed $website) => is_array($website))
            ->sortByDesc(fn (array $website) => (bool) ($website['primair'] ?? false))
            ->pluck('url')
            ->map(fn (mixed $website) => trim((string) $website))
            ->first(fn (string $website) => $website !== '');
    }

    public function getLat(): ?float
    {
        return $this->latFromAddress($this->adresMetCoordinaten());
    }

    public function getLon(): ?float
    {
        return $this->lonFromAddress($this->adresMetCoordinaten());
    }

    private function latFromAddress(?array $address): ?float
    {
        $value = data_get($address, 'locatie.coordinates.1')
            ?? data_get($address, 'coordinates.1')
            ?? data_get($address, 'locatie.y')
            ?? data_get($address, 'y')
            ?? data_get($address, 'geocoordinatenX')
            ?? data_get($address, 'lat')
            ?? data_get($address, 'latitude');

        if (! is_numeric($value)) {
            return null;
        }

        $lat = (float) $value;

        return $lat >= -90 && $lat <= 90 ? $lat : null;
    }

    private function lonFromAddress(?array $address): ?float
    {
        $value = data_get($address, 'locatie.coordinates.0')
            ?? data_get($address, 'coordinates.0')
            ?? data_get($address, 'locatie.x')
            ?? data_get($address, 'x')
            ?? data_get($address, 'geocoordinatenY')
            ?? data_get($address, 'lon')
            ?? data_get($address, 'lng')
            ?? data_get($address, 'longitude');

        if (! is_numeric($value)) {
            return null;
        }

        $lon = (float) $value;

        return $lon >= -180 && $lon <= 180 ? $lon : null;
    }
}

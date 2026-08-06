<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use MongoDB\Laravel\Eloquent\Model;

class SiteSetting extends Model
{
    public const COLLECTION = 'site_settings';

    public const REACTIVE_SEARCH_KEY = 'reactive_search_enabled';

    public const CONTACT_EMAIL_KEY = 'contact_email';

    public const DEFAULT_CONTACT_EMAIL = 'admin@jongerenkaart.be';

    private const CACHE_PREFIX = 'site_setting:';

    protected $connection = 'mongodb';

    protected $collection = self::COLLECTION;

    public $timestamps = true;

    protected $guarded = [];

    public static function reactiveSearchEnabled(): bool
    {
        return Cache::rememberForever(
            self::cacheKey(self::REACTIVE_SEARCH_KEY),
            fn (): bool => filter_var(
                self::storedValue(self::REACTIVE_SEARCH_KEY, true),
                FILTER_VALIDATE_BOOLEAN,
            ),
        );
    }

    public static function setReactiveSearchEnabled(bool $enabled): void
    {
        self::query()->updateOrCreate(
            ['key' => self::REACTIVE_SEARCH_KEY],
            ['value' => $enabled],
        );

        Cache::forever(self::cacheKey(self::REACTIVE_SEARCH_KEY), $enabled);
    }

    public static function contactEmail(): string
    {
        return Cache::rememberForever(
            self::cacheKey(self::CONTACT_EMAIL_KEY),
            fn (): string => (string) self::storedValue(self::CONTACT_EMAIL_KEY, self::DEFAULT_CONTACT_EMAIL),
        );
    }

    public static function setContactEmail(string $email): void
    {
        $email = strtolower(trim($email));

        self::query()->updateOrCreate(
            ['key' => self::CONTACT_EMAIL_KEY],
            ['value' => $email],
        );

        Cache::forever(self::cacheKey(self::CONTACT_EMAIL_KEY), $email);
    }

    private static function storedValue(string $key, mixed $default): mixed
    {
        return self::query()->where('key', $key)->value('value') ?? $default;
    }

    private static function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX.$key;
    }
}

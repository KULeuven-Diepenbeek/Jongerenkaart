<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ContentPage extends Model
{
    public const COLLECTION = 'content_pages';

    protected $connection = 'mongodb';

    protected $collection = self::COLLECTION;

    public $timestamps = true;

    protected $guarded = [];

    public static function editablePages(): array
    {
        return [
            'info' => 'Info',
            'privacy' => 'Privacy',
            'faq' => 'FAQ',
        ];
    }

    public static function findBySlug(string $slug): ?self
    {
        return self::query()->where('slug', $slug)->first();
    }
}

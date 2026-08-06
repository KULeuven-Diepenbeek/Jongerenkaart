<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TrafficVisitor extends Model
{
    public const COLLECTION = 'traffic_visitors';

    protected $connection = 'mongodb';

    protected $collection = self::COLLECTION;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'first_seen' => 'datetime',
            'last_seen' => 'datetime',
            'authenticated' => 'boolean',
        ];
    }
}

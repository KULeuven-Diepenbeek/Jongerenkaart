<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TrafficSnapshot extends Model
{
    public const COLLECTION = 'traffic_snapshots';

    public const DASHBOARD_KEY = 'admin_dashboard';

    protected $connection = 'mongodb';

    protected $collection = self::COLLECTION;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }
}

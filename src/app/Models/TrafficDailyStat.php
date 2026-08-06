<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TrafficDailyStat extends Model
{
    public const COLLECTION = 'traffic_daily_stats';

    protected $connection = 'mongodb';

    protected $collection = self::COLLECTION;

    public $timestamps = false;

    protected $guarded = [];
}

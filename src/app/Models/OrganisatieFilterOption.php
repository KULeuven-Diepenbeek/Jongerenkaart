<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class OrganisatieFilterOption extends Model
{
    public const COLLECTION = 'organisatie_filter_options';

    protected $connection = 'mongodb';

    protected $collection = self::COLLECTION;

    public $timestamps = true;

    protected $guarded = [];
}

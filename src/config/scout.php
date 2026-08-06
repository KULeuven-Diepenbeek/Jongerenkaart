<?php

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', false),

    'after_commit' => false,

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            'organisaties' => [
                'searchableAttributes' => [
                    'name',
                    'category',
                    'subcategory',
                    'subsubcategory',
                    'description',
                    'context',
                ],
                'filterableAttributes' => [
                    'category',
                    'leeftijdscategorie',
                    'ages',
                    'rubriek',
                    'werkingsgebieden',
                    'taalAanbod',
                    'betalingswijze',
                    'isFree',
                    'isAnonymous',
                    'aanbod',
                ],
                'displayedAttributes' => [
                    'id',
                    'name',
                    'category',
                    'subcategory',
                    'subsubcategory',
                    'description',
                    'leeftijdscategorie',
                    'ages',
                    'rubriek',
                    'werkingsgebieden',
                    'taalAanbod',
                    'betalingswijze',
                    'isFree',
                    'isAnonymous',
                    'aanbod',
                ],
            ],
        ],
    ],
];

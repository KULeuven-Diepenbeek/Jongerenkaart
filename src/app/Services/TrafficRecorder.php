<?php

namespace App\Services;

use App\Models\TrafficDailyStat;
use App\Models\TrafficVisitor;
use Illuminate\Http\Request;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection as MongoCollection;

class TrafficRecorder
{
    private const AUTHENTICATED_ACTIVITY = 'authenticated_activity';

    private static bool $indexesEnsured = false;

    private const PUBLIC_PAGE_ROUTES = [
        'home',
        'search',
        'map',
        'organisaties.show',
        'info',
        'contact',
        'faq',
        'privacy',
        'login',
        'register.create',
    ];

    public function eventType(Request $request): ?string
    {
        $routeName = $request->route()?->getName();

        return match (true) {
            $request->isMethod('GET') && in_array($routeName, self::PUBLIC_PAGE_ROUTES, true) => 'page_view',
            $request->isMethod('GET') && $routeName === 'search.results' => 'search_request',
            $request->isMethod('GET') && $routeName === 'map.results' => 'map_request',
            (bool) $request->user() => self::AUTHENTICATED_ACTIVITY,
            default => null,
        };
    }

    public function isBot(Request $request): bool
    {
        return (bool) preg_match(
            '/bot|crawler|spider|slurp|bingpreview|facebookexternalhit|monitoring|uptime/i',
            (string) $request->userAgent(),
        );
    }

    public function visitorHash(string $visitorId): string
    {
        return hash_hmac('sha256', $visitorId, (string) config('app.key'));
    }

    public function record(
        Request $request,
        string $eventType,
        string $visitorHash,
        int $statusCode,
        int $durationMs,
    ): void {
        $this->ensureIndexes();

        $now = now();
        $routeName = (string) ($request->route()?->getName() ?? 'unknown');
        $device = $this->deviceType($request);
        $isBot = $this->isBot($request);
        $increments = [];

        if ($eventType === self::AUTHENTICATED_ACTIVITY) {
            $this->recordActiveVisitor($request, $visitorHash, $routeName, $device, $now);

            return;
        }

        if ($isBot) {
            $increments['bot_requests'] = 1;
        } else {
            $increments['response_time_total_ms'] = max(0, $durationMs);
            $increments['response_samples'] = 1;
            $increments[$eventType.'s'] = 1;
            $increments['devices.'.$device] = 1;

            if ($statusCode >= 400) {
                $increments['error_responses'] = 1;
            }

            if ($request->user()) {
                $increments['authenticated_requests'] = 1;
            }

            if ($eventType === 'page_view') {
                $increments['routes.'.$this->safeKey($routeName)] = 1;
            }
        }

        $update = [
            '$inc' => $increments,
            '$set' => [
                'updated_at' => new UTCDateTime($now),
            ],
            '$setOnInsert' => [
                'date' => $now->copy()->timezone('Europe/Brussels')->toDateString(),
                'created_at' => new UTCDateTime($now),
            ],
        ];

        if (! $isBot) {
            $update['$addToSet'] = ['visitor_hashes' => $visitorHash];
        }

        TrafficDailyStat::query()->raw(
            fn (MongoCollection $collection) => $collection->updateOne(
                ['date' => $now->copy()->timezone('Europe/Brussels')->toDateString()],
                $update,
                ['upsert' => true],
            ),
        );

        if ($isBot) {
            return;
        }

        $this->recordActiveVisitor($request, $visitorHash, $routeName, $device, $now);
    }

    private function recordActiveVisitor(
        Request $request,
        string $visitorHash,
        string $routeName,
        string $device,
        $now,
    ): void {
        TrafficVisitor::query()->raw(
            fn (MongoCollection $collection) => $collection->updateOne(
                ['visitor_hash' => $visitorHash],
                [
                    '$set' => [
                        'last_seen' => new UTCDateTime($now),
                        'authenticated' => (bool) $request->user(),
                        'user_id' => $request->user() ? (string) $request->user()->getAuthIdentifier() : null,
                        'route' => $routeName,
                        'device' => $device,
                    ],
                    '$setOnInsert' => [
                        'visitor_hash' => $visitorHash,
                        'first_seen' => new UTCDateTime($now),
                    ],
                ],
                ['upsert' => true],
            ),
        );
    }

    public function recordOrganisationChange(string $action): void
    {
        if (! in_array($action, ['created', 'updated', 'deleted'], true)) {
            return;
        }

        $this->ensureIndexes();

        $now = now();
        $date = $now->copy()->timezone('Europe/Brussels')->toDateString();

        TrafficDailyStat::query()->raw(
            fn (MongoCollection $collection) => $collection->updateOne(
                ['date' => $date],
                [
                    '$inc' => ['organisation_changes.'.$action => 1],
                    '$set' => ['updated_at' => new UTCDateTime($now)],
                    '$setOnInsert' => [
                        'date' => $date,
                        'created_at' => new UTCDateTime($now),
                    ],
                ],
                ['upsert' => true],
            ),
        );
    }

    private function deviceType(Request $request): string
    {
        $userAgent = (string) $request->userAgent();

        return match (true) {
            (bool) preg_match('/ipad|tablet|kindle|silk/i', $userAgent) => 'tablet',
            (bool) preg_match('/mobile|android|iphone|ipod/i', $userAgent) => 'mobile',
            default => 'desktop',
        };
    }

    private function safeKey(string $value): string
    {
        return str_replace(['.', '$'], ['_', ''], $value);
    }

    private function ensureIndexes(): void
    {
        if (self::$indexesEnsured) {
            return;
        }

        TrafficDailyStat::query()->raw(function (MongoCollection $collection): void {
            $collection->createIndex(['date' => 1], ['unique' => true]);
        });
        TrafficVisitor::query()->raw(function (MongoCollection $collection): void {
            $collection->createIndex(['visitor_hash' => 1], ['unique' => true]);
            $collection->createIndex(['last_seen' => 1]);
        });

        self::$indexesEnsured = true;
    }
}

<?php

namespace App\Services;

use App\Models\Organisatie;
use App\Models\TrafficDailyStat;
use App\Models\TrafficSnapshot;
use App\Models\TrafficVisitor;
use App\Models\User;
use Illuminate\Support\Collection;
use MongoDB\Collection as MongoCollection;

class TrafficDashboardService
{
    private const RETENTION_DAYS = 400;

    public function refresh(string $generatedBy): TrafficSnapshot
    {
        TrafficSnapshot::query()->raw(function (MongoCollection $collection): void {
            $collection->createIndex(['key' => 1], ['unique' => true]);
        });

        $today = now('Europe/Brussels')->startOfDay();
        $from30Days = $today->copy()->subDays(29)->toDateString();
        $dailyStats = TrafficDailyStat::query()
            ->where('date', '>=', $from30Days)
            ->orderBy('date')
            ->get();
        $activeVisitors = TrafficVisitor::query()
            ->where('last_seen', '>=', now()->subMinutes(5))
            ->get();
        $sevenDays = $this->since($dailyStats, $today->copy()->subDays(6)->toDateString());
        $thirtyDays = $this->since($dailyStats, $from30Days);
        $todayStats = $this->since($dailyStats, $today->toDateString());
        $users = User::query()->get();
        $trackingStartedAt = TrafficDailyStat::query()->orderBy('date')->value('date');

        $data = [
            'summary' => [
                'concurrent_visitors' => $activeVisitors->count(),
                'concurrent_logins' => $activeVisitors->where('authenticated', true)->pluck('user_id')->filter()->unique()->count(),
                'today_page_views' => $this->sum($todayStats, 'page_views'),
                'today_unique_visitors' => $this->uniqueVisitors($todayStats),
                'seven_day_page_views' => $this->sum($sevenDays, 'page_views'),
                'seven_day_unique_visitors' => $this->uniqueVisitors($sevenDays),
                'thirty_day_page_views' => $this->sum($thirtyDays, 'page_views'),
                'thirty_day_unique_visitors' => $this->uniqueVisitors($thirtyDays),
                'seven_day_search_requests' => $this->sum($sevenDays, 'search_requests'),
                'seven_day_map_requests' => $this->sum($sevenDays, 'map_requests'),
                'seven_day_average_response_ms' => $this->averageResponseTime($sevenDays),
                'seven_day_errors' => $this->sum($sevenDays, 'error_responses'),
                'seven_day_bot_requests' => $this->sum($sevenDays, 'bot_requests'),
                'organisations' => Organisatie::query()->count(),
                'users' => $users->count(),
                'admins' => $users->where('role', User::ROLE_ADMIN)->count(),
                'contact_users' => $users->where('role', User::ROLE_CONTACT)->count(),
            ],
            'daily' => $this->dailyTrend($dailyStats, $today),
            'top_pages' => $this->topPages($thirtyDays),
            'devices' => $this->deviceCounts($thirtyDays),
            'organisation_changes' => [
                'created' => $this->sum($thirtyDays, 'organisation_changes.created'),
                'updated' => $this->sum($thirtyDays, 'organisation_changes.updated'),
                'deleted' => $this->sum($thirtyDays, 'organisation_changes.deleted'),
            ],
            'tracking_started_at' => $trackingStartedAt,
        ];

        $snapshot = TrafficSnapshot::query()->updateOrCreate(
            ['key' => TrafficSnapshot::DASHBOARD_KEY],
            [
                'data' => $data,
                'generated_at' => now(),
                'generated_by' => $generatedBy,
            ],
        );

        $this->pruneOldTrackingData();

        return $snapshot->fresh();
    }

    private function since(Collection $stats, string $date): Collection
    {
        return $stats->filter(fn (TrafficDailyStat $stat) => (string) $stat->date >= $date)->values();
    }

    private function sum(Collection $stats, string $field): int
    {
        return (int) $stats->sum(fn (TrafficDailyStat $stat) => (int) data_get($stat->getAttributes(), $field, 0));
    }

    private function uniqueVisitors(Collection $stats): int
    {
        return $stats
            ->flatMap(fn (TrafficDailyStat $stat) => (array) ($stat->visitor_hashes ?? []))
            ->filter()
            ->unique()
            ->count();
    }

    private function averageResponseTime(Collection $stats): int
    {
        $samples = $this->sum($stats, 'response_samples');

        return $samples > 0
            ? (int) round($this->sum($stats, 'response_time_total_ms') / $samples)
            : 0;
    }

    private function dailyTrend(Collection $stats, $today): array
    {
        $byDate = $stats->keyBy(fn (TrafficDailyStat $stat) => (string) $stat->date);

        return collect(range(13, 0))
            ->map(function (int $daysAgo) use ($byDate, $today) {
                $date = $today->copy()->subDays($daysAgo);
                $stat = $byDate->get($date->toDateString());

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->translatedFormat('d M'),
                    'page_views' => (int) ($stat?->page_views ?? 0),
                    'unique_visitors' => count(array_unique((array) ($stat?->visitor_hashes ?? []))),
                ];
            })
            ->all();
    }

    private function topPages(Collection $stats): array
    {
        $counts = [];

        foreach ($stats as $stat) {
            foreach ((array) ($stat->routes ?? []) as $route => $count) {
                $counts[$route] = ($counts[$route] ?? 0) + (int) $count;
            }
        }

        arsort($counts);

        return collect($counts)
            ->take(10)
            ->map(fn (int $views, string $route) => [
                'route' => $route,
                'label' => $this->routeLabel($route),
                'views' => $views,
            ])
            ->values()
            ->all();
    }

    private function deviceCounts(Collection $stats): array
    {
        return collect(['mobile', 'tablet', 'desktop'])
            ->mapWithKeys(fn (string $device) => [$device => $this->sum($stats, 'devices.'.$device)])
            ->all();
    }

    private function routeLabel(string $route): string
    {
        return [
            'home' => 'Home',
            'search' => 'Zoeken',
            'map' => 'Kaart',
            'organisaties_show' => 'Organisatiedetail',
            'info' => 'Info',
            'contact' => 'Contact',
            'faq' => 'FAQ',
            'privacy' => 'Privacy',
            'login' => 'Inloggen',
            'register_create' => 'Account aanmaken',
        ][$route] ?? str_replace('_', ' ', ucfirst($route));
    }

    private function pruneOldTrackingData(): void
    {
        TrafficDailyStat::query()
            ->where('date', '<', now('Europe/Brussels')->subDays(self::RETENTION_DAYS)->toDateString())
            ->delete();
        TrafficVisitor::query()
            ->where('last_seen', '<', now()->subDays(90))
            ->delete();
    }
}

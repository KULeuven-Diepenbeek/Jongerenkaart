<?php

namespace App\Http\Middleware;

use App\Services\TrafficRecorder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordTraffic
{
    private const VISITOR_COOKIE = 'jk_visitor_id';

    public function __construct(
        private readonly TrafficRecorder $recorder,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('traffic_started_at', hrtime(true));
        $eventType = $this->recorder->eventType($request);
        $user = $request->user();
        $visitorId = $user
            ? 'user:'.(string) $user->getAuthIdentifier()
            : (string) $request->cookie(self::VISITOR_COOKIE, '');

        if ($eventType && ! $user && ! $this->recorder->isBot($request) && $visitorId === '') {
            $visitorId = (string) Str::uuid();
        }

        if ($eventType) {
            $request->attributes->set('traffic_event_type', $eventType);
            $request->attributes->set(
                'traffic_visitor_hash',
                $visitorId !== '' ? $this->recorder->visitorHash($visitorId) : '',
            );
        }

        $response = $next($request);

        if ($eventType && ! $user && ! $this->recorder->isBot($request) && ! $request->hasCookie(self::VISITOR_COOKIE)) {
            $response->headers->setCookie(
                Cookie::create(self::VISITOR_COOKIE)
                    ->withValue($visitorId)
                    ->withExpires(now()->addYear())
                    ->withPath('/')
                    ->withSecure($request->isSecure())
                    ->withHttpOnly(true)
                    ->withSameSite(Cookie::SAMESITE_LAX),
            );
        }

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        $eventType = $request->attributes->get('traffic_event_type');
        $visitorHash = $request->attributes->get('traffic_visitor_hash');
        $startedAt = $request->attributes->get('traffic_started_at');

        if (! is_string($eventType) || ! is_string($visitorHash) || ! is_int($startedAt)) {
            return;
        }

        try {
            $durationMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);
            $this->recorder->record($request, $eventType, $visitorHash, $response->getStatusCode(), $durationMs);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsFresh
{
    public const AUTHENTICATED_AT = 'authenticated_at';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $authenticatedAt = $request->session()->get(self::AUTHENTICATED_AT);

        if (! is_numeric($authenticatedAt)) {
            $request->session()->put(self::AUTHENTICATED_AT, now()->timestamp);

            return $next($request);
        }

        $maximumAge = max((int) config('session.absolute_lifetime', 60), 1) * 60;

        if ((now()->timestamp - (int) $authenticatedAt) < $maximumAge) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Je sessie is verlopen. Log opnieuw in.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Je sessie is na één uur verlopen. Log opnieuw in.');
    }
}

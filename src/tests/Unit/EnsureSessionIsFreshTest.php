<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureSessionIsFresh;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EnsureSessionIsFreshTest extends TestCase
{
    public function test_authenticated_session_younger_than_one_hour_is_allowed(): void
    {
        $request = $this->authenticatedRequest(now()->subMinutes(59)->timestamp);

        $response = (new EnsureSessionIsFresh)->handle(
            $request,
            fn () => new Response('allowed'),
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_authenticated_session_expires_after_one_hour(): void
    {
        Auth::shouldReceive('logout')->once();
        $request = $this->authenticatedRequest(now()->subHour()->timestamp);
        $request->headers->set('Accept', 'application/json');

        $response = (new EnsureSessionIsFresh)->handle(
            $request,
            fn () => new Response('allowed'),
        );

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame('Je sessie is verlopen. Log opnieuw in.', $response->getData(true)['message']);
    }

    public function test_existing_session_without_login_timestamp_starts_a_new_limit(): void
    {
        $request = $this->authenticatedRequest();

        (new EnsureSessionIsFresh)->handle(
            $request,
            fn () => new Response('allowed'),
        );

        $this->assertSame(
            now()->timestamp,
            $request->session()->get(EnsureSessionIsFresh::AUTHENTICATED_AT),
        );
    }

    private function authenticatedRequest(?int $authenticatedAt = null): Request
    {
        $request = Request::create('/mijn-account');
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(60)));
        $request->setUserResolver(fn () => new User([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]));

        if ($authenticatedAt !== null) {
            $request->session()->put(EnsureSessionIsFresh::AUTHENTICATED_AT, $authenticatedAt);
        }

        return $request;
    }
}

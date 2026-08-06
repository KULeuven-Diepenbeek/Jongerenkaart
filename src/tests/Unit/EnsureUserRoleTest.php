<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserRoleTest extends TestCase
{
    public function test_admin_can_pass_admin_role_middleware(): void
    {
        $request = $this->requestForRole(User::ROLE_ADMIN);

        $response = (new EnsureUserRole)->handle(
            $request,
            fn () => new Response('allowed'),
            User::ROLE_ADMIN,
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('allowed', $response->getContent());
    }

    public function test_contact_person_cannot_pass_admin_role_middleware(): void
    {
        $this->assertForbidden($this->requestForRole(User::ROLE_CONTACT));
    }

    public function test_guest_cannot_pass_role_middleware(): void
    {
        $request = Request::create('/beheer/instellingen');
        $request->setUserResolver(fn () => null);

        $this->assertForbidden($request);
    }

    private function requestForRole(string $role): Request
    {
        $user = new User(['role' => $role]);
        $request = Request::create('/beheer/instellingen');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    private function assertForbidden(Request $request): void
    {
        try {
            (new EnsureUserRole)->handle(
                $request,
                fn () => new Response('allowed'),
                User::ROLE_ADMIN,
            );

            $this->fail('The role middleware allowed a forbidden request.');
        } catch (HttpException $exception) {
            $this->assertSame(Response::HTTP_FORBIDDEN, $exception->getStatusCode());
        }
    }
}

<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSessionIsFresh;
use App\Models\User;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use Tests\TestCase;

class OrganisationApiAuthenticationTest extends TestCase
{
    public function test_all_organisation_api_routes_require_session_authentication(): void
    {
        foreach ([
            ['GET', '/api/organisaties'],
            ['POST', '/api/organisaties'],
            ['GET', '/api/organisaties/507f1f77bcf86cd799439011'],
            ['PUT', '/api/organisaties/507f1f77bcf86cd799439011'],
            ['PATCH', '/api/organisaties/507f1f77bcf86cd799439011'],
            ['DELETE', '/api/organisaties/507f1f77bcf86cd799439011'],
        ] as [$method, $uri]) {
            $route = app('router')->getRoutes()->match(Request::create($uri, $method));
            $middleware = $route->gatherMiddleware();

            $this->assertContains('web', $middleware, $method.' '.$uri.' must start a session.');
            $this->assertContains('auth', $middleware, $method.' '.$uri.' must require authentication.');
            $this->assertContains('session.fresh', $middleware, $method.' '.$uri.' must enforce the one-hour limit.');
        }
    }

    public function test_guest_cannot_read_organisation_api(): void
    {
        $this->getJson('/api/organisaties')->assertUnauthorized();
    }

    public function test_contact_person_cannot_create_an_organisation_through_api(): void
    {
        $user = new User([
            '_id' => new ObjectId,
            'name' => 'Contact',
            'email' => 'contact@example.test',
            'role' => User::ROLE_CONTACT,
            'organisatie_ids' => [],
            'is_active' => true,
        ]);

        $this
            ->actingAs($user)
            ->withSession([EnsureSessionIsFresh::AUTHENTICATED_AT => now()->timestamp])
            ->postJson('/api/organisaties', ['publiekeNaam' => 'Niet toegestaan'])
            ->assertForbidden();
    }
}

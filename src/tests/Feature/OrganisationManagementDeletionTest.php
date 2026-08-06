<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSessionIsFresh;
use App\Models\Organisatie;
use App\Models\User;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use Tests\TestCase;

class OrganisationManagementDeletionTest extends TestCase
{
    public function test_delete_route_requires_admin_role(): void
    {
        $route = app('router')->getRoutes()->match(
            Request::create('/beheer/organisaties/507f1f77bcf86cd799439011', 'DELETE'),
        );

        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('session.fresh', $route->gatherMiddleware());
        $this->assertContains('role:admin', $route->gatherMiddleware());
    }

    public function test_contact_person_cannot_delete_an_organisation(): void
    {
        $this
            ->actingAs($this->user(User::ROLE_CONTACT))
            ->withSession([EnsureSessionIsFresh::AUTHENTICATED_AT => now()->timestamp])
            ->delete('/beheer/organisaties/507f1f77bcf86cd799439011')
            ->assertForbidden();
    }

    public function test_admin_sees_delete_action_in_organisation_list(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN));

        $organisatie = new Organisatie([
            'publiekeNaam' => 'Verwijderbare organisatie',
        ]);
        $organisatie->setAttribute('_id', new ObjectId);

        $this->view('manage.organisations.index', [
            'organisaties' => collect([$organisatie]),
            'query' => '',
        ])
            ->assertSee('Verwijder')
            ->assertSee(route('manage.organisations.destroy', [
                'organisatie' => (string) $organisatie->getKey(),
            ]), false);
    }

    public function test_contact_person_does_not_see_delete_action(): void
    {
        $this->actingAs($this->user(User::ROLE_CONTACT));

        $organisatie = new Organisatie([
            'publiekeNaam' => 'Enkel bewerkbare organisatie',
        ]);
        $organisatie->setAttribute('_id', new ObjectId);

        $this->view('manage.organisations.index', [
            'organisaties' => collect([$organisatie]),
            'query' => '',
        ])->assertDontSee('Verwijder');
    }

    private function user(string $role): User
    {
        return new User([
            '_id' => new ObjectId,
            'name' => 'Testgebruiker',
            'email' => $role.'@example.test',
            'role' => $role,
            'organisatie_ids' => [],
            'is_active' => true,
        ]);
    }
}

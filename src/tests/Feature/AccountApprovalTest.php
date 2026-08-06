<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSessionIsFresh;
use App\Models\Organisatie;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use MongoDB\BSON\ObjectId;
use Tests\TestCase;

class AccountApprovalTest extends TestCase
{
    private const EMAIL = 'approval-test@jongerenkaart.test';

    private ?Organisatie $organisatie = null;

    protected function tearDown(): void
    {
        User::query()->where('email', self::EMAIL)->delete();

        if ($this->organisatie?->exists) {
            Organisatie::withoutSyncingToSearch(
                fn () => $this->organisatie->delete(),
            );
        }

        parent::tearDown();
    }

    public function test_registration_requires_admin_activation_before_login(): void
    {
        $this->organisatie = new Organisatie([
            'publiekeNaam' => 'Testorganisatie accountgoedkeuring',
            'contactgegevens' => [
                'emailadressen' => [
                    ['adres' => self::EMAIL, 'primair' => true],
                ],
            ],
        ]);
        Organisatie::withoutSyncingToSearch(
            fn () => $this->organisatie->save(),
        );

        $this->post('/account-aanmaken', [
            'name' => 'Nieuwe contactpersoon',
            'email' => self::EMAIL,
            'password' => 'VeiligWachtwoord123!',
            'password_confirmation' => 'VeiligWachtwoord123!',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $user = User::query()->where('email', self::EMAIL)->firstOrFail();

        $this->assertFalse($user->isActive());
        $this->assertGuest();

        $this->post('/login', [
            'email' => self::EMAIL,
            'password' => 'VeiligWachtwoord123!',
        ])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $admin = new User([
            '_id' => new ObjectId,
            'name' => 'Testadmin',
            'email' => 'admin-approval-test@jongerenkaart.test',
            'password' => Hash::make('irrelevant'),
            'role' => User::ROLE_ADMIN,
            'organisatie_ids' => [],
            'is_active' => true,
        ]);

        $this
            ->actingAs($admin)
            ->withSession([EnsureSessionIsFresh::AUTHENTICATED_AT => now()->timestamp])
            ->post(route('admin.users.activate', ['user' => (string) $user->getKey()]))
            ->assertRedirect();

        $this->assertTrue($user->fresh()->isActive());
        $this->assertNotNull($user->fresh()->approved_at);
    }
}

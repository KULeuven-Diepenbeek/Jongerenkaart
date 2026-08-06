<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'organisatie_ids', 'is_active', 'approved_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CONTACT = 'contactpersoon';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'organisatie_ids' => 'array',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isContactpersoon(): bool
    {
        return $this->role === self::ROLE_CONTACT;
    }

    public function isActive(): bool
    {
        return ! array_key_exists('is_active', $this->getAttributes())
            || (bool) $this->is_active;
    }

    public function canManageOrganisatie(string $organisatieId): bool
    {
        return $this->isAdmin()
            || in_array($organisatieId, $this->organisatie_ids ?? [], true);
    }
}

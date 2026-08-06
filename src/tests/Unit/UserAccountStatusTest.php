<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserAccountStatusTest extends TestCase
{
    public function test_new_pending_account_is_inactive(): void
    {
        $user = new User(['is_active' => false]);

        $this->assertFalse($user->isActive());
    }

    public function test_approved_account_is_active(): void
    {
        $user = new User(['is_active' => true]);

        $this->assertTrue($user->isActive());
    }

    public function test_legacy_account_without_status_remains_active(): void
    {
        $user = new User;

        $this->assertTrue($user->isActive());
    }
}

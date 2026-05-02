<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $user = new User();
        $user->role = 'admin';

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_user_role(): void
    {
        $user = new User();
        $user->role = 'user';

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_user_returns_true_for_user_role(): void
    {
        $user = new User();
        $user->role = 'user';

        $this->assertTrue($user->isUser());
    }

    public function test_is_user_returns_false_for_admin_role(): void
    {
        $user = new User();
        $user->role = 'admin';

        $this->assertFalse($user->isUser());
    }
}

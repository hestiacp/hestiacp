<?php
declare(strict_types=1);

namespace App\Tests\Users\User;

use App\Users\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCanBeHydratedAndDehydrated(): void
    {
        $state = [

        ];

        $user = User::hydrate($state);

        $this->assertSame($state, $user->dehydate());
    }
}

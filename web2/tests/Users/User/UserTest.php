<?php
declare(strict_types=1);

namespace App\Tests\Users\User;

use App\Users\User\AuthenticationSettings;
use App\Users\User\ContactInfo;
use App\Users\User\PanelSettings;
use App\Users\User\Password;
use App\Users\User\Role;
use App\Users\User\ServerSettings;
use App\Users\User\User;
use App\Users\User\Username;
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

    public function testUserCanBeAdded(): void
    {
        $user = User::add(
            new Username('Username'),
            new Password('Password'),
            new ContactInfo(
                'contact name',
                'email@example.com',
            ),
            PanelSettings::initial('en'),
            AuthenticationSettings::initial(true),
            new ServerSettings(
                'nologin',
                '8.3',
                [
                    'ns1.example.com',
                    'ns2.example.com',
                ]
            ),
        );

        $this->assertInstanceOf(User::class, $user);
    }

    public function testUserCanBeChanged(): void
    {
        $user = User::add(
            new Username('Username'),
            new Password('Password'),
            new ContactInfo(
                'contact name',
                'email@example.com',
            ),
            PanelSettings::initial('en'),
            AuthenticationSettings::initial(true),
            new ServerSettings(
                'nologin',
                '8.3',
                [
                    'ns1.example.com',
                    'ns2.example.com',
                ]
            ),
        );

        $user->change(
            new Password('Password'),
            new Role('user'),
            new ContactInfo(
                'contact name',
                'email@example.com',
            ),
            new PanelSettings('en', 'dark', 'date'),
            new AuthenticationSettings(true, true, false, []),
            new ServerSettings(
                'nologin',
                '8.3',
                [
                    'ns1.example.com',
                    'ns2.example.com',
                ]
            ),
        );

        $this->assertInstanceOf(User::class, $user);
    }
}

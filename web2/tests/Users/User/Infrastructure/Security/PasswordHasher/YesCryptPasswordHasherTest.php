<?php
declare(strict_types=1);

namespace App\Tests\Users\User\Infrastructure\Security\PasswordHasher;

use App\Users\User\Infrastructure\Security\PasswordHasher\YesCryptPasswordHasher;
use PHPUnit\Framework\TestCase;

class YesCryptPasswordHasherTest extends TestCase
{
	private YesCryptPasswordHasher $passwordHasher;

	public function setUp(): void
	{
		$this->passwordHasher = new YesCryptPasswordHasher();
	}

	/**
	 * @group integration
	 */
	public function testHasherCanHashYesCryptPasswords(): void
	{
		$hashedPassword = $this->passwordHasher->hash('test-password');

		$this->assertStringStartsWith('$y$', $hashedPassword);
	}

	/**
	 * @group integration
	 */
	public function testHasherCanHashPasswordsStartingWithCliValues(): void
	{
		$hashedPassword = $this->passwordHasher->hash('--test-password');

		$this->assertStringStartsWith('$y$', $hashedPassword);
	}

	/**
	 * @group integration
	 */
	public function testHasherCanVerifyPassword(): void
	{
		$plainPassword = 'test-password';
		$hashedPassword = $this->passwordHasher->hash($plainPassword);

		$this->assertTrue($this->passwordHasher->verify($hashedPassword, $plainPassword));
	}
}

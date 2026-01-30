<?php
declare(strict_types=1);

namespace App\Tests\Users\User\Infrastructure\Storage;

use App\System\Hestia\Infrastructure\Cli\HestiaCommandRunner;
use App\Users\User\Application\Query\SecurityUser;
use App\Users\User\Infrastructure\Storage\HestiaUserQueryRepository;
use PHPUnit\Framework\TestCase;

class HestiaUserQueryRepositoryTest extends TestCase
{
	private HestiaUserQueryRepository $queryRepository;

	public function setUp(): void
	{
		$this->queryRepository = new HestiaUserQueryRepository(
			new HestiaCommandRunner($_ENV['HESTIA_BIN_DIR']),
		);
	}

	/**
	 * @group integration
	 */
    public function testNotExistingUserReturnsNull(): void
    {
		$user = $this->queryRepository->findSecurityUserByUsername('not-existing-user');

		$this->assertNull($user);
    }

	/**
	 * @group integration
	 */
	public function testExistingUserReturnsUser(): void
	{
		$user = $this->queryRepository->findSecurityUserByUsername('admin');

		$this->assertInstanceOf(SecurityUser::class, $user);
	}
}

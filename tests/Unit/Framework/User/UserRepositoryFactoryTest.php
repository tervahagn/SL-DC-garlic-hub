<?php

namespace Tests\Unit\Framework\User;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use App\Framework\User\UserRepositoryFactory;
use App\Framework\Core\Config\Config;
use Doctrine\DBAL\Connection;
use App\Framework\User\Edge\UserMainRepository;
use App\Framework\User\Core\UserAclRepository;
use App\Framework\User\Core\UserContactRepository;
use App\Framework\User\Core\UserStatsRepository;
use App\Framework\User\Enterprise\UserVipRepository;
use App\Framework\User\Enterprise\UserSecurityRepository;

class UserRepositoryFactoryTest extends TestCase
{
	private Config $configMock;
	private Connection $connectionMock;
	private UserRepositoryFactory $factory;

	protected function setUp(): void
	{
		$this->configMock = $this->createMock(Config::class);
		$this->connectionMock = $this->createMock(Connection::class);
		$this->factory = new UserRepositoryFactory($this->configMock, $this->connectionMock);
	}

	#[Group('units')]
	public function testCreateEnterpriseEdition(): void
	{
		$this->configMock->method('getEdition')->willReturn(Config::PLATFORM_EDITION_ENTERPRISE);

		$result = $this->factory->create();

		$this->assertArrayHasKey('main', $result);
		$this->assertInstanceOf(UserMainRepository::class, $result['main']);

		$this->assertArrayHasKey('acl', $result);
		$this->assertInstanceOf(UserAclRepository::class, $result['acl']);

		$this->assertArrayHasKey('contact', $result);
		$this->assertInstanceOf(UserContactRepository::class, $result['contact']);

		$this->assertArrayHasKey('stats', $result);
		$this->assertInstanceOf(UserStatsRepository::class, $result['stats']);

		$this->assertArrayHasKey('vip', $result);
		$this->assertInstanceOf(UserVipRepository::class, $result['vip']);

		$this->assertArrayHasKey('security', $result);
		$this->assertInstanceOf(UserSecurityRepository::class, $result['security']);
	}

	#[Group('units')]
	public function testCreateCoreEdition(): void
	{
		$this->configMock->method('getEdition')->willReturn(Config::PLATFORM_EDITION_CORE);

		$result = $this->factory->create();

		$this->assertArrayHasKey('main', $result);
		$this->assertInstanceOf(UserMainRepository::class, $result['main']);

		$this->assertArrayHasKey('acl', $result);
		$this->assertInstanceOf(UserAclRepository::class, $result['acl']);

		$this->assertArrayHasKey('contact', $result);
		$this->assertInstanceOf(UserContactRepository::class, $result['contact']);

		$this->assertArrayHasKey('stats', $result);
		$this->assertInstanceOf(UserStatsRepository::class, $result['stats']);

		$this->assertArrayNotHasKey('vip', $result);
		$this->assertArrayNotHasKey('security', $result);
	}

	#[Group('units')]
	public function testCreateEdgeEdition(): void
	{
		$this->configMock->method('getEdition')->willReturn(Config::PLATFORM_EDITION_EDGE);

		$result = $this->factory->create();

		$this->assertArrayHasKey('main', $result);
		$this->assertInstanceOf(UserMainRepository::class, $result['main']);

		$this->assertArrayNotHasKey('acl', $result);
		$this->assertArrayNotHasKey('contact', $result);
		$this->assertArrayNotHasKey('stats', $result);
		$this->assertArrayNotHasKey('vip', $result);
		$this->assertArrayNotHasKey('security', $result);
	}
}

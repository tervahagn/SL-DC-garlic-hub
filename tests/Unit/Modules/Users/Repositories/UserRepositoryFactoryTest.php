<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Tests\Unit\Modules\Users\Repositories;

use App\Framework\Core\Config\Config;
use App\Modules\Users\Repositories\Core\UserContactRepository;
use App\Modules\Users\Repositories\Core\UserStatsRepository;
use App\Modules\Users\Repositories\Edge\UserAclRepository;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Repositories\Enterprise\UserSecurityRepository;
use App\Modules\Users\Repositories\Enterprise\UserVipRepository;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

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
		$this->assertArrayHasKey('acl', $result);
		$this->assertInstanceOf(UserAclRepository::class, $result['acl']);

		$this->assertArrayNotHasKey('contact', $result);
		$this->assertArrayNotHasKey('stats', $result);
		$this->assertArrayNotHasKey('vip', $result);
		$this->assertArrayNotHasKey('security', $result);
	}
}

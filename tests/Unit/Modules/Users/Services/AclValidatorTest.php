<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace Tests\Unit\Modules\Users\Services;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Users\Services\AclValidator;
use App\Modules\Users\Entities\UserEntity;
use App\Modules\Users\Services\UsersService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AclValidatorTest extends TestCase
{
	private readonly AclValidator $aclValidator;
	private readonly UsersService $userServiceMock;
	private readonly Config $configMock;

	protected function setUp(): void
	{
		$this->userServiceMock = $this->createMock(UsersService::class);
		$this->configMock = $this->createMock(Config::class);
		$this->aclValidator = new AclValidator('users', $this->userServiceMock, $this->configMock);
	}

	/**
	 * @return void
	 */
	#[Group('units')]
	public function testcheckModuleName(): void
	{
		$this->assertSame('users', $this->aclValidator->getModuleName());
	}
}

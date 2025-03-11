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

namespace Tests\Unit\Modules\Users\Services;

use App\Modules\Users\Entities\UserEntity;
use App\Modules\Users\Entities\UserEntityFactory;
use App\Modules\Users\Repositories\Edge\UserMainRepository;
use App\Modules\Users\Repositories\UserRepositoryFactory;
use App\Modules\Users\Services\UsersService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Phpfastcache\Helper\Psr16Adapter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

class UserServiceTest extends TestCase
{
	private UsersService $userService;
	private UserEntityFactory $entityFactoryMock;
	private Psr16Adapter $cacheMock;
	private UserMainRepository $userMainRepositoryMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$repositoryFactoryMock       = $this->createMock(UserRepositoryFactory::class);
		$this->entityFactoryMock     = $this->createMock(UserEntityFactory::class);
		$this->cacheMock             = $this->createMock(Psr16Adapter::class);
		$this->userMainRepositoryMock = $this->createMock(UserMainRepository::class);
		$repositoryFactoryMock->method('create')
			->willReturn(['main' => $this->userMainRepositoryMock]);

		$this->userService = new UsersService(
			$repositoryFactoryMock,
			$this->entityFactoryMock,
			$this->cacheMock
		);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdatePassword(): void
	{
		$this->userMainRepositoryMock->method('update')->with($this->isInt(), $this->isArray())->willReturn(12);

		$this->assertEquals(12, $this->userService->updatePassword(1, 'hurz'));

	}


	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindUserSuccess(): void
	{
		$identifier = 'test@example.com';
		$mockUserData = ['UID' => 1, 'username' => 'testuser'];

		$this->userMainRepositoryMock
			->method('findByIdentifier')
			->with($identifier)
			->willReturn($mockUserData);

		$result = $this->userService->findUser($identifier);

		$this->assertEquals($mockUserData, $result);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindUserNotFound(): void
	{
		$identifier = 'unknown@example.com';

		// Repository simuliert, dass kein Benutzer gefunden wurde
		$this->userMainRepositoryMock->method('findByIdentifier')
			->with($identifier)
			->willReturn([]);

		$this->assertEmpty($this->userService->findUser($identifier));
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testGetCurrentUserFromCache(): void
	{
		$UID = 1;
		$cachedData = ['UID' => 1, 'username' => 'testuser'];

		$this->cacheMock->method('get')->with("user_$UID")
			->willReturn($cachedData);

		$this->userMainRepositoryMock->expects($this->never())->method('findById');

		$mockUserEntity = $this->createMock(UserEntity::class);
		$this->entityFactoryMock->method('create')
			->with($cachedData)
			->willReturn($mockUserEntity);

		$result = $this->userService->getUserById($UID);
		$this->assertEquals($mockUserEntity, $result);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testGetCurrentUserFromDatabase(): void
	{
		$UID = 1;
		$userData = ['UID' => 1, 'username' => 'testuser'];

		$this->cacheMock->method('get')->with("user_$UID")
			->willReturn(null);

		$this->userMainRepositoryMock->expects($this->once())->method('findById')->with($UID)
			->willReturn($userData);

		$mockUserEntity = $this->createMock(UserEntity::class);
		$this->entityFactoryMock->method('create')
			->with(['main' => $userData])
			->willReturn($mockUserEntity);

		$result = $this->userService->getUserById($UID);

		$this->assertEquals($mockUserEntity, $result);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	#[Group('units')]
	public function testInvalidCache(): void
	{
		$UID = 14;

		$this->cacheMock->expects($this->once())->method('delete')
			->with('user_'.$UID)
		;
		$this->userService->invalidateCache($UID);

	}


}

<?php

namespace Tests\Unit\Framework\User;

use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\Framework\User\UserService;
use App\Framework\User\UserEntityFactory;
use App\Framework\User\UserRepositoryFactory;
use App\Framework\User\UserEntity;
use App\Framework\User\Edge\UserMainRepository;
use Phpfastcache\Helper\Psr16Adapter;
use App\Framework\Exceptions\UserException;

class UserServiceTest extends TestCase
{
	private UserService $userService;
	private UserRepositoryFactory $mockRepositoryFactory;
	private UserEntityFactory $mockEntityFactory;
	private Psr16Adapter $mockCache;
	private UserMainRepository $mockUserMainRepository;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->mockRepositoryFactory = $this->createMock(UserRepositoryFactory::class);
		$this->mockEntityFactory     = $this->createMock(UserEntityFactory::class);
		$this->mockCache             = $this->createMock(Psr16Adapter::class);
		$this->mockUserMainRepository = $this->createMock(UserMainRepository::class);
		$this->mockRepositoryFactory->method('create')
			->willReturn(['main' => $this->mockUserMainRepository]);

		$this->userService = new UserService(
			$this->mockRepositoryFactory,
			$this->mockEntityFactory,
			$this->mockCache
		);
	}

	#[Group('units')]
	public function testFindUserSuccess(): void
	{
		$identifier = 'test@example.com';
		$mockUserData = ['UID' => 1, 'username' => 'testuser'];

		// Repository simuliert Rückgabe von User-Daten
		$this->mockUserMainRepository
			->method('findByIdentifier')
			->with($identifier)
			->willReturn($mockUserData);

		$result = $this->userService->findUser($identifier);

		$this->assertEquals($mockUserData, $result);
	}

	#[Group('units')]
	public function testFindUserNotFound(): void
	{
		$identifier = 'unknown@example.com';

		// Repository simuliert, dass kein Benutzer gefunden wurde
		$this->mockUserMainRepository->method('findByIdentifier')
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

		$this->mockCache->method('get')->with("user_$UID")
			->willReturn($cachedData);

		$this->mockUserMainRepository->expects($this->never())->method('findById');

		$mockUserEntity = $this->createMock(UserEntity::class);
		$this->mockEntityFactory->method('create')
			->with($cachedData)
			->willReturn($mockUserEntity);

		$result = $this->userService->getCurrentUser($UID);
		$this->assertEquals($mockUserEntity, $result);
	}

	#[Group('units')]
	public function testGetCurrentUserFromDatabase(): void
	{
		$UID = 1;
		$userData = ['UID' => 1, 'username' => 'testuser'];

		$this->mockCache->method('get')->with("user_$UID")
			->willReturn(null);

		$this->mockUserMainRepository->expects($this->once())->method('findById')->with($UID)
			->willReturn($userData);

		$mockUserEntity = $this->createMock(UserEntity::class);
		$this->mockEntityFactory->method('create')
			->with(['main' => $userData])
			->willReturn($mockUserEntity);

		$result = $this->userService->getCurrentUser($UID);

		$this->assertEquals($mockUserEntity, $result);
	}

	#[Group('units')]
	public function testInvalidCache(): void
	{
		$UID = 14;

		$this->mockCache->expects($this->once())->method('delete')
			->with('user_'.$UID)
		;
		$this->userService->invalidateCache($UID);

	}


}

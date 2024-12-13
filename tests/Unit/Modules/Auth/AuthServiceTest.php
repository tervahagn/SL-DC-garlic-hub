<?php

namespace Tests\Unit\Modules\Auth;

use App\Framework\Exceptions\UserException;
use App\Framework\User\UserEntity;
use App\Framework\User\UserService;
use App\Modules\Auth\AuthService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;

class AuthServiceTest extends TestCase
{
	private AuthService $authServiceMock;
	private UserService $userServiceMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->userServiceMock = $this->createMock(UserService::class);

		$this->authServiceMock = new AuthService($this->userServiceMock);
	}

	/**
	 * @throws UserException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testSuccessfulLogin(): void
	{
		$identifier = 'test@example.com';
		$password = 'correct_password';
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		$userData = ['UID' => 1, 'password' => $hashedPassword];

		$userEntityMock = $this->createMock(UserEntity::class);

		// Verhalten des UserService simulieren
		$this->userServiceMock->method('findUser')->with($identifier)->willReturn($userData);
		$this->userServiceMock->method('getCurrentUser')->with($userData['UID'])->willReturn($userEntityMock);

		$result = $this->authServiceMock->login($identifier, $password);

		$this->assertInstanceOf(UserEntity::class, $result);
		$this->assertEquals($userEntityMock, $result);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoginWithInvalidCredentials(): void
	{
		$identifier = 'test@example.com';
		$password = 'wrong_password';
		$hashedPassword = password_hash('correct_password', PASSWORD_DEFAULT);
		$userData = ['UID' => 1, 'password' => $hashedPassword];

		// Verhalten des UserService simulieren
		$this->userServiceMock->method('findUser')->with($identifier)->willReturn($userData);

		$this->expectException(UserException::class);
		$this->expectExceptionMessage('Invalid credentials.');

		$this->authServiceMock->login($identifier, $password);
	}


	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws InvalidArgumentException
	 */
	#[Group('units')]
	public function testLoginWithNonExistentUser(): void
	{
		$identifier = 'nonexistent@example.com';
		$password   = 'password';
		$this->userServiceMock->method('findUser')->with($identifier)->willReturn([]);

		$this->expectException(UserException::class);
		$this->expectExceptionMessage('Invalid credentials.');

		$this->authServiceMock->login($identifier, $password);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	#[Group('units')]
	public function testLogout(): void
	{
		$this->userServiceMock->expects($this->once())->method('invalidateCache')->with(45);

		$this->authServiceMock->logout(['UID' => 45]);
	}
}

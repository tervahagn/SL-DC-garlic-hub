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

namespace Tests\Unit\Modules\Auth;

use App\Framework\Core\Cookie;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\UserException;
use App\Modules\Auth\AuthService;
use App\Modules\Users\Entities\UserEntity;
use App\Modules\Users\Services\UsersService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class AuthServiceTest extends TestCase
{
	private AuthService $authService;
	private UsersService $userServiceMock;
	private Cookie $cookieMock;
	private LoggerInterface $loggerMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->userServiceMock = $this->createMock(UsersService::class);
		$this->cookieMock      = $this->createMock(Cookie::class);
		$this->loggerMock      = $this->createMock(LoggerInterface::class);

		$this->authService = new AuthService(
			$this->userServiceMock,
			$this->cookieMock,
			$this->loggerMock
		);
	}

	/**
	 * @throws Exception
	 * @throws UserException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoginSuccess(): void
	{
		$identifier = 'user@example.com';
		$password = 'correct_password';
		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
		$userData = [
			'UID' => 1,
			'password' => $hashedPassword,
			'status' => UsersService::USER_STATUS_REGULAR,
		];

		$this->userServiceMock->method('findUser')->with($identifier)->willReturn($userData);
		$userEntityMock = $this->createMock(UserEntity::class);
		$this->userServiceMock->method('getUserById')->with(1)->willReturn($userEntityMock);

		$userEntity = $this->authService->login($identifier, $password);

		$this->assertInstanceOf(UserEntity::class, $userEntity);
		$this->assertEmpty($this->authService->getErrorMessage());
	}

	#[Group('units')]
	public function testLoginInvalidCredentials(): void
	{
		$identifier = 'user@example.com';
		$password = 'wrong_password';

		$this->userServiceMock->method('findUser')->with($identifier)->willReturn([
			'UID' => 1,
			'password' => password_hash('correct_password', PASSWORD_BCRYPT),
		]);

		$userEntity = $this->authService->login($identifier, $password);

		$this->assertNull($userEntity);
		$this->assertEquals('Invalid credentials.', $this->authService->getErrorMessage());
	}

	#[Group('units')]
	public function testLoginUserDeleted(): void
	{
		$identifier = 'deleted@example.com';
		$password = 'irrelevant_password';

		$this->userServiceMock->method('findUser')->with($identifier)->willReturn([
			'UID' => 1,
			'password' => password_hash($password, PASSWORD_BCRYPT),
			'status' => UsersService::USER_STATUS_DELETED,
		]);

		$userEntity = $this->authService->login($identifier, $password);

		$this->assertNull($userEntity);
		$this->assertEquals('login//account_deleted', $this->authService->getErrorMessage());
	}

	#[Group('units')]
	public function testLoginUserNotActive(): void
	{
		$identifier = 'deleted@example.com';
		$password = 'irrelevant_password';

		$this->userServiceMock->method('findUser')->with($identifier)->willReturn([
			'UID' => 1,
			'password' => password_hash($password, PASSWORD_BCRYPT),
			'status' => UsersService::USER_STATUS_REGISTERED,
		]);

		$userEntity = $this->authService->login($identifier, $password);

		$this->assertNull($userEntity);
		$this->assertEquals('login//account_inactive', $this->authService->getErrorMessage());
	}

	/**
	 * @throws UserException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testLoginByCookieSuccess(): void
	{
		$cookiePayload = ['UID' => 1, 'sid' => 'valid_session'];
		$userEntityMock = $this->createMock(UserEntity::class);

		$this->cookieMock->method('hasCookie')->with(AuthService::COOKIE_NAME_AUTO_LOGIN)->willReturn(true);
		$this->cookieMock->method('getHashedCookie')->with(AuthService::COOKIE_NAME_AUTO_LOGIN)->willReturn($cookiePayload);

		$userEntityMock->method('getMain')->willReturn(['status' => UsersService::USER_STATUS_REGULAR]);
		$this->userServiceMock->method('getUserById')->with(1)->willReturn($userEntityMock);

		$userEntity = $this->authService->loginByCookie();

		$this->assertInstanceOf(UserEntity::class, $userEntity);
	}

	#[Group('units')]
	public function testLoginByCookieNoCookie(): void
	{
		$this->cookieMock->method('hasCookie')->with(AuthService::COOKIE_NAME_AUTO_LOGIN)->willReturn(false);

		$userEntity = $this->authService->loginByCookie();

		$this->assertNull($userEntity);
		$this->assertEquals('No cookie for autologin was found.', $this->authService->getErrorMessage());
	}

	#[Group('units')]
	public function testLoginByCookieNoUID(): void
	{
		$cookiePayload = ['UID' => 0, 'sid' => 'valid_session'];
		$userEntityMock = $this->createMock(UserEntity::class);

		$this->cookieMock->method('hasCookie')->with(AuthService::COOKIE_NAME_AUTO_LOGIN)->willReturn(true);
		$this->cookieMock->method('getHashedCookie')->with(AuthService::COOKIE_NAME_AUTO_LOGIN)->willReturn($cookiePayload);

		$userEntityMock->method('getMain')->willReturn(['status' => UsersService::USER_STATUS_REGULAR]);
		$this->userServiceMock->method('getUserById')->with(1)->willReturn($userEntityMock);

		$userEntity = $this->authService->loginByCookie();

		$this->assertNull($userEntity);
		$this->assertEquals('No valid UID found.', $this->authService->getErrorMessage());
	}


	/**
	 * @throws Exception
	 * @throws UserException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoginSilentSuccess(): void
	{
		$UID = 1;
		$sessionId = 'valid_session';
		$userEntityMock = $this->createMock(UserEntity::class);

		$userEntityMock->method('getMain')->willReturn(['status' => UsersService::USER_STATUS_REGULAR]);

		$this->userServiceMock->method('getUserById')->with($UID)->willReturn($userEntityMock);

		$userEntity = $this->authService->loginSilent($UID, $sessionId);

		$this->assertInstanceOf(UserEntity::class, $userEntity);
		$this->assertEmpty($this->authService->getErrorMessage());
	}

	/**
	 * @throws Exception
	 * @throws UserException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoginSilentUserLocked(): void
	{
		$UID = 1;
		$sessionId = 'valid_session';
		$userEntityMock = $this->createMock(UserEntity::class);

		$userEntityMock->method('getMain')->willReturn(['status' => UsersService::USER_STATUS_LOCKED]);

		$this->userServiceMock->method('getUserById')->with($UID)->willReturn($userEntityMock);

		$userEntity = $this->authService->loginSilent($UID, $sessionId);

		$this->assertNull($userEntity);
		$this->assertEquals('login//account_locked', $this->authService->getErrorMessage());
	}

	/**
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testCreateAutologinCookie(): void
	{
		$payload = ['UID' => 45, 'sid' => 'the_session_id'];
		$this->cookieMock->expects($this->once())->method('createHashedCookie')
			->with(
				AuthService::COOKIE_NAME_AUTO_LOGIN,
				$payload,
				$this->anything());

		$this->authService->createAutologinCookie(45, 'the_session_id');
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	#[Group('units')]
	public function testLogout(): void
	{
		$this->userServiceMock->expects($this->once())->method('invalidateCache')->with(45);

		$this->authService->logout(['UID' => 45]);
	}
}

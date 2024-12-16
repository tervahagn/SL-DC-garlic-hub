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

use App\Framework\Exceptions\UserException;
use App\Framework\User\UserEntity;
use App\Modules\Auth\AuthService;
use App\Modules\Auth\LoginController;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use SlimSession\Helper;

class LoginControllerTest extends TestCase
{
	private ServerRequestInterface $requestMock;
	private ResponseInterface $responseMock;
	private Helper $sessionMock;
	private AuthService $authServiceMock;
	private LoggerInterface $loggerMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->requestMock     = $this->createMock(ServerRequestInterface::class);
		$this->responseMock    = $this->createMock(ResponseInterface::class);
		$this->sessionMock     = $this->createMock(Helper::class);
		$this->authServiceMock = $this->createMock(AuthService::class);
		$this->loggerMock      = $this->createMock(LoggerInterface::class);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testShowLoginRedirectsToHomeIfUserInSession(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('exists')->with('user')->willReturn(true);
		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', '/')->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new LoginController($this->authServiceMock, $this->loggerMock);
		$result = $controller->showLogin($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testShowLoginRendersFormIfUserNotInSession(): void
	{
		$flash = $this->createMock(Messages::class);
		$this->requestMock->method('getAttribute')
					  ->willReturnOnConsecutiveCalls($this->sessionMock, $flash)
		;
		$this->sessionMock->method('exists')->with('user')->willReturn(false);

		$messages = ['error' => 'Invalid credentials.'];
		$flash->expects($this->once())->method('getMessages')->willReturn($messages);

		$body = $this->createMock(StreamInterface::class);
		$this->responseMock->method('getBody')->willReturn($body);

		$body->expects($this->once())->method('write');
		$this->responseMock->expects($this->once())->method('withHeader')->with('Content-Type', 'text/html')->willReturnSelf();

		$controller = new LoginController($this->authServiceMock, $this->loggerMock);
		$result = $controller->showLogin($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}

	/**
	 * @throws Exception
	 * @throws UserException
	 */
	#[Group('units')]
	public function testLoginRedirectsToHomeOnSuccessfulLogin(): void
	{
		$flash = $this->createMock(Messages::class);
		$userEntity = $this->createMock(UserEntity::class);

		$this->requestMock->method('getParsedBody')->willReturn(['username' => 'testuser', 'password' => 'password']);
		$this->requestMock->method('getAttribute')->willReturnOnConsecutiveCalls($this->sessionMock, $flash);
		$this->authServiceMock->method('login')->with('testuser', 'password')->willReturn($userEntity);

		$main_data = ['locale' => 'kl_KL'];
		$userEntity->method('getMain')->willReturn($main_data);

		$this->sessionMock->expects($this->exactly(2))->method('set');

		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', '/')->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();
		$this->sessionMock->expects($this->once())->method('exists')->with('oauth_redirect_params')->willReturn(false);
		$this->sessionMock->expects($this->never())->method('get');


		$controller = new LoginController($this->authServiceMock, $this->loggerMock);
		$result = $controller->login($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}

	/**
	 * @throws Exception
	 * @throws UserException
	 */
	#[Group('units')]
	public function testLoginRedirectsToApiOnSuccessfulLogin(): void
	{
		$flash = $this->createMock(Messages::class);
		$userEntity = $this->createMock(UserEntity::class);

		$this->requestMock->method('getParsedBody')->willReturn(['username' => 'testuser', 'password' => 'password']);
		$this->requestMock->method('getAttribute')->willReturnOnConsecutiveCalls($this->sessionMock, $flash);
		$this->authServiceMock->method('login')->with('testuser', 'password')->willReturn($userEntity);

		$main_data = ['locale' => 'kl_KL'];
		$userEntity->method('getMain')->willReturn($main_data);

		$this->sessionMock->expects($this->exactly(2))->method('set');

		$this->sessionMock->expects($this->once())->method('exists')->with('oauth_redirect_params')->willReturn(true);
		$this->sessionMock->expects($this->once())->method('get')->willReturn(['some' => 'stuff']);
		$this->sessionMock->expects($this->once())->method('delete')->with('oauth_redirect_params');


		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', '/api/authorize?some=stuff')->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new LoginController($this->authServiceMock, $this->loggerMock);
		$result = $controller->login($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}





	/**
	 * @throws Exception
	 * @throws UserException
	 */
	#[Group('units')]
	public function testLoginRedirectsToLoginOnInvalidCredentials(): void
	{
		$flash      = $this->createMock(Messages::class);

		$this->requestMock->method('getParsedBody')->willReturn(['username' => 'testuser', 'password' => 'wrong_password']);
		$this->requestMock->method('getAttribute')->willReturnOnConsecutiveCalls($this->sessionMock, $flash);

		$this->authServiceMock->method('login')->with('testuser', 'wrong_password')->willThrowException(new
		UserException('Invalid credentials.'));

		$flash->expects($this->once())->method('addMessage')->with('error', 'Invalid credentials.');

		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', '/login')->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();
		$this->loggerMock->expects($this->once())->method('error')->with('Invalid credentials.');

		$controller = new LoginController($this->authServiceMock, $this->loggerMock);
		$result = $controller->login($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}

	/**
	 * @return void
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	#[Group('units')]
	public function testLogoutRedirectsToLogin(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->expects($this->once())->method('get')->with('user')->willReturn(['UID' => 88]);
		$this->sessionMock->expects($this->once())->method('delete')->with('user');
		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', '/login')->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new LoginController($this->authServiceMock, $this->loggerMock);
		$result = $controller->logout($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}
}

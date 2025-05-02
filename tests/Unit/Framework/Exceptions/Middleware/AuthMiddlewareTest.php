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

namespace Tests\Unit\Framework\Exceptions\Middleware;

use App\Framework\Core\Cookie;
use App\Framework\Core\Session;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\UserException;
use App\Framework\Middleware\AuthMiddleware;
use App\Modules\Auth\AuthService;
use App\Modules\Users\Entities\UserEntity;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddlewareTest extends TestCase
{
	private ServerRequestInterface $requestMock;
	private RequestHandlerInterface $handlerMock;

	private AuthService $authServiceMock;
	private Session $sessionMock;
	private Cookie $cookieMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->authServiceMock = $this->createMock(AuthService::class);
		$this->sessionMock = $this->createMock(Session::class);
		$this->cookieMock = $this->createMock(Cookie::class);
		$this->handlerMock = $this->createMock(RequestHandlerInterface::class);
		$this->requestMock = $this->createMock(ServerRequestInterface::class);
	}

	/**
	 * @throws UserException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testProcessHandlesPublicRoutes(): void
	{
		$uriInterfaceMock = $this->createMock(UriInterface::class);
		$this->requestMock->method('getUri')->willReturn($uriInterfaceMock);
		$uriInterfaceMock->method('getPath')->willReturn('/register');
		$this->requestMock->expects($this->never())->method('getAttribute');

		$this->handlerMock
			->expects($this->once())
			->method('handle')
			->with($this->requestMock)
			->willReturn(new Response());

		$middleware = new AuthMiddleware($this->authServiceMock);
		$response = $middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testProcessRedirectsToLoginIfSessionNull(): void
	{
		$this->mockSecurePage();

		$this->requestMock->method('getAttribute')->with('session')->willReturn(null);

		$middleware = new AuthMiddleware($this->authServiceMock);
		$response = $middleware->process($this->requestMock, $this->handlerMock);

		$this->assertEquals(302, $response->getStatusCode());
		$this->assertEquals(['/login'], $response->getHeader('Location'));
	}

	#[Group('units')]
	public function testProcessRedirectsToLoginIfNotAuth(): void
	{
		$this->mockSecurePage();
		$this->requestMock->method('getAttribute')
			->willReturnCallback(function ($param)
			{
				if ($param === 'cookie')
					return $this->cookieMock;
				elseif ($param === 'session')
					return $this->sessionMock;
				return null;
			}
		);

		$this->sessionMock->method('exists')->with('user')->willReturn(false);

		$middleware = new AuthMiddleware($this->authServiceMock);
		$response = $middleware->process($this->requestMock, $this->handlerMock);

		$this->assertEquals(302, $response->getStatusCode());
		$this->assertEquals(['/login'], $response->getHeader('Location'));
	}

	#[Group('units')]
	public function testProcessHandlesAuthenticatedUser(): void
	{
		$this->mockSecurePage();
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('exists')->with('user')->willReturn(true);

		$this->handlerMock
			->expects($this->once())
			->method('handle')
			->with($this->requestMock)
			->willReturn(new Response());

		$middleware = new AuthMiddleware($this->authServiceMock);
		$response = $middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws UserException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws FrameworkException
	 */
	#[Group('units')]
	public function testProcessAutoLoginWithCookie(): void
	{
		$this->mockSecurePage();
		$this->requestMock->method('getAttribute')
			->willReturnCallback(function ($param)
			{
				if ($param === 'cookie')
					return $this->cookieMock;
				elseif ($param === 'session')
					return $this->sessionMock;
				return null;
			}
		);

		$this->sessionMock->method('exists')->with('user')->willReturn(false);

		$userEntityMock = $this->createMock(UserEntity::class);
		$this->authServiceMock->method('loginByCookie')->willReturn($userEntityMock);
		$this->cookieMock->method('hasCookie')->with(AuthService::COOKIE_NAME_AUTO_LOGIN)->willReturn(true);
		$userEntityMock->method('getMain')->willReturn(['UID' => 1, 'locale' => 'en']);

		$this->sessionMock->expects($this->exactly(2))->method('set');

		$this->handlerMock->expects($this->once())->method('handle')
			->with($this->requestMock)
			->willReturn(new Response());

		$middleware = new AuthMiddleware($this->authServiceMock);
		$response = $middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testProcessAutoLoginWithCookieAndLogin(): void
	{
		$uriInterfaceMock = $this->createMock(UriInterface::class);
		$this->requestMock->method('getUri')->willReturn($uriInterfaceMock);
		$uriInterfaceMock->method('getPath')->willReturn('/login');

		$this->requestMock->method('getAttribute')
			->willReturnCallback(function ($param)
			{
				if ($param === 'cookie')
					return $this->cookieMock;
				elseif ($param === 'session')
					return $this->sessionMock;
				return null;
			}
			);

		$this->sessionMock->method('exists')->with('user')->willReturn(false);

		$userEntityMock = $this->createMock(UserEntity::class);
		$this->authServiceMock->method('loginByCookie')->willReturn($userEntityMock);
		$this->cookieMock->method('hasCookie')->with(AuthService::COOKIE_NAME_AUTO_LOGIN)->willReturn(true);
		$userEntityMock->method('getMain')->willReturn(['UID' => 1, 'locale' => 'en']);

		$this->sessionMock->expects($this->exactly(2))->method('set');

		$middleware = new AuthMiddleware($this->authServiceMock);
		$response = $middleware->process($this->requestMock, $this->handlerMock);

		$this->assertEquals(302, $response->getStatusCode());
		$this->assertEquals(['/'], $response->getHeader('Location'));
	}


	/**
	 * @throws Exception
	 */
	private function mockSecurePage()
	{
		$uriInterfaceMock = $this->createMock(UriInterface::class);
		$this->requestMock->method('getUri')->willReturn($uriInterfaceMock);
		$uriInterfaceMock->method('getPath')->willReturn('/secure-page');
	}

}

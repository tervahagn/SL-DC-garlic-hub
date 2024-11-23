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

namespace Tests\Unit\Modules\Auth\Controller;

use App\Framework\Exceptions\UserException;
use App\Modules\Auth\Controller\LoginController;
use App\Modules\Auth\Entities\User;
use App\Modules\Auth\Repositories\UserMain;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Flash\Messages;
use SlimSession\Helper;

class LoginControllerTest extends TestCase
{
	private ServerRequestInterface $request;
	private ResponseInterface $response;
	private Helper $session;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->request  = $this->createMock(ServerRequestInterface::class);
		$this->response = $this->createMock(ResponseInterface::class);
		$this->session  = $this->createMock(Helper::class);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testShowLoginRedirectsToHomeIfUserInSession(): void
	{
		$this->request->method('getAttribute')->with('session')->willReturn($this->session);
		$this->session->method('exists')->with('user')->willReturn(true);
		$this->response->expects($this->once())->method('withHeader')->with('Location', '/')->willReturnSelf();
		$this->response->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new LoginController($this->createMock(UserMain::class));
		$result = $controller->showLogin($this->request, $this->response);

		$this->assertSame($this->response, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testShowLoginRendersFormIfUserNotInSession(): void
	{
		$flash = $this->createMock(Messages::class);
		$this->request->method('getAttribute')
					  ->willReturnOnConsecutiveCalls($this->session, $flash)
		;
		$this->session->method('exists')->with('user')->willReturn(false);

		$messages = ['error' => 'Invalid credentials.'];
		$flash->expects($this->once())->method('getMessages')->willReturn($messages);

		$body = $this->createMock(StreamInterface::class);
		$this->response->method('getBody')->willReturn($body);

		$body->expects($this->once())->method('write');
		$this->response->expects($this->once())->method('withHeader')->with('Content-Type', 'text/html')->willReturnSelf();

		$controller = new LoginController($this->createMock(UserMain::class));
		$result = $controller->showLogin($this->request, $this->response);

		$this->assertSame($this->response, $result);
	}

	/**
	 * @throws Exception
	 * @throws UserException
	 */
	#[Group('units')]
	public function testLoginRedirectsToHomeOnSuccessfulLogin(): void
	{
		$flash = $this->createMock(Messages::class);
		$userMain = $this->createMock(UserMain::class);
		$user = $this->createMock(User::class);

		$this->request->method('getParsedBody')->willReturn(['username' => 'testuser', 'password' => 'password']);
		$this->request->method('getAttribute')->willReturnOnConsecutiveCalls($this->session, $flash);
		$userMain->method('loadUserByIdentifier')->with('testuser')->willReturn($user);
		$user->method('getPassword')->willReturn(password_hash('password', PASSWORD_DEFAULT));
		$this->response->expects($this->once())->method('withHeader')->with('Location', '/')->willReturnSelf();
		$this->response->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new LoginController($userMain);
		$result = $controller->login($this->request, $this->response);

		$this->assertSame($this->response, $result);
	}

	/**
	 * @throws Exception
	 * @throws UserException
	 */
	#[Group('units')]
	public function testLoginRedirectsToLoginOnInvalidCredentials(): void
	{
		$flash = $this->createMock(Messages::class);
		$userMain = $this->createMock(UserMain::class);
		$user = $this->createMock(User::class);

		$this->request->method('getParsedBody')->willReturn(['username' => 'testuser', 'password' => 'wrongpassword']);
		$this->request->method('getAttribute')->willReturnOnConsecutiveCalls($this->session, $flash);

		$userMain->method('loadUserByIdentifier')->with('testuser')->willReturn($user);
		$user->method('getPassword')->willReturn(password_hash('password', PASSWORD_DEFAULT));
		$flash->expects($this->once())->method('addMessage')->with('error', 'Invalid credentials.');
		$this->response->expects($this->once())->method('withHeader')->with('Location', '/login')->willReturnSelf();
		$this->response->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new LoginController($userMain);
		$result = $controller->login($this->request, $this->response);

		$this->assertSame($this->response, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testLogoutRedirectsToLogin(): void
	{
		$this->request->method('getAttribute')->with('session')->willReturn($this->session);
		$this->session->expects($this->once())->method('delete')->with('user');
		$this->response->expects($this->once())->method('withHeader')->with('Location', '/login')->willReturnSelf();
		$this->response->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new LoginController($this->createMock(UserMain::class));
		$result = $controller->logout($this->request, $this->response);

		$this->assertSame($this->response, $result);
	}
}

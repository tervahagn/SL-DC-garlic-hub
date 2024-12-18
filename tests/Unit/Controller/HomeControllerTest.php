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

namespace Tests\Unit\Controller;

use App\Controller\HomeController;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use SlimSession\Helper;

class HomeControllerTest extends TestCase
{
	private ServerRequestInterface $mockRequest;
	private ResponseInterface $mockResponse;
	private Helper $mockSession;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->mockRequest  = $this->createMock(ServerRequestInterface::class);
		$this->mockResponse = $this->createMock(ResponseInterface::class);
		$this->mockSession  = $this->createMock(Helper::class);
	}

	#[Group('units')]
	public function testIndexRedirectsToLoginIfUserNotInSession(): void
	{
		$this->mockRequest->method('getAttribute')->with('session')->willReturn($this->mockSession);
		$this->mockSession->method('exists')->with('user')->willReturn(false);
		$this->mockResponse->expects($this->once())->method('withHeader')->with('Location', '/login')->willReturnSelf();
		$this->mockResponse->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new HomeController();
		$result = $controller->index($this->mockRequest, $this->mockResponse);

		$this->assertSame($this->mockResponse, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testIndexReturnsHomePageIfUserInSession(): void
	{
		$this->mockRequest->method('getAttribute')->with('session')->willReturn($this->mockSession);
		$this->mockSession->method('exists')->with('user')->willReturn(true);
		$this->mockSession->method('get')->with('user')->willReturn(['username' => 'testuser']);
		$this->mockResponse->method('getBody')->willReturn($this->createMock(StreamInterface::class));
		$this->mockResponse->expects($this->once())->method('withHeader')->with('Content-Type', 'text/html')->willReturnSelf();

		$controller = new HomeController();
		$result = $controller->index($this->mockRequest, $this->mockResponse);

		$this->assertSame($this->mockResponse, $result);
	}
}

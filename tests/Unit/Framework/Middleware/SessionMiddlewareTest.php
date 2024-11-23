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

namespace Tests\Unit\Framework\Middleware;

use App\Framework\Middleware\SessionMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SlimSession\Helper;

class SessionMiddlewareTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testProcessAddsSessionAndFlashAttributesToRequest(): void
	{
		session_start(); // Todo-> Put this shit to integration tests or find a way to not new Messages
		$sessionMock = $this->createMock(Helper::class);
		$requestMock = $this->createMock(ServerRequestInterface::class);
		$responseMock = $this->createMock(ResponseInterface::class);
		$handlerMock = $this->createMock(RequestHandlerInterface::class);

		// Mock the `withAttribute` method to ensure attributes are added
		$requestMock->expects($this->exactly(2))
					->method('withAttribute')
		         	->willReturnCallback(fn($key, $value) => $requestMock);


		// Ensure the handler's `handle` method is called with the modified request
		$handlerMock->expects($this->once())
					->method('handle')
					->with($requestMock)
					->willReturn($responseMock);

		$middleware = new SessionMiddleware($sessionMock);
		$result = $middleware->process($requestMock, $handlerMock);

		// Assert that the response from the handler is returned unchanged
		$this->assertSame($responseMock, $result);

		session_destroy();
	}
}

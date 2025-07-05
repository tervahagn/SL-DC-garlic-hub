<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace Tests\Unit\Framework\Middleware;

use App\Framework\Core\Config\Config;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Translate\Translator;
use App\Framework\Middleware\EnvironmentMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EnvironmentMiddlewareTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testProcessAddsAttributesAndCallsNextHandler(): void
	{
		$configMock     = $this->createMock(Config::class);
		$localesMock    = $this->createMock(Locales::class);
		$translatorMock = $this->createMock(Translator::class);

		$localesMock->expects($this->once())->method('determineCurrentLocale');

		$requestMock = $this->createMock(ServerRequestInterface::class);
		$requestMock->method('withAttribute')->willReturnSelf();

		$responseMock = $this->createMock(ResponseInterface::class);

		$handlerMock = $this->createMock(RequestHandlerInterface::class);
		$handlerMock->expects($this->once())
					->method('handle')
					->with($this->isInstanceOf(ServerRequestInterface::class))
					->willReturn($responseMock);

		$middleware = new EnvironmentMiddleware($configMock, $localesMock, $translatorMock);
		$result     = $middleware->process($requestMock, $handlerMock);

		$this->assertSame($responseMock, $result);
	}
}

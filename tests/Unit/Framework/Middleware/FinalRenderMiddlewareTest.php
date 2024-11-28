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

use App\Framework\Middleware\FinalRenderMiddleware;
use App\Framework\TemplateEngine\AdapterInterface;
use App\Framework\TemplateEngine\TemplateService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FinalRenderMiddlewareTest extends TestCase
{
	private FinalRenderMiddleware $middleware;
	private AdapterInterface $templateServiceMock;
	private ServerRequestInterface $requestMock ;
	private ResponseInterface $responseMock;
	private RequestHandlerInterface $handlerMock;
	private UriInterface $uriInterfaceMock;

	/**
	 * @throws \Exception|Exception
	 */
	protected function setUp(): void
	{
		$this->templateServiceMock = $this->createMock(AdapterInterface::class);
		$this->requestMock = $this->createMock(ServerRequestInterface::class);
		$this->responseMock = $this->createMock(ResponseInterface::class);
		$this->handlerMock = $this->createMock(RequestHandlerInterface::class);
		$this->uriInterfaceMock = $this->createMock(UriInterface::class);
		$this->requestMock->method('getUri')->willReturn($this->uriInterfaceMock);

		$this->middleware = new FinalRenderMiddleware($this->templateServiceMock);
	}

	#[Group('units')]
	public function testProcessReturnsResponseForApiRoute(): void
	{
		$this->handlerMock->method('handle')->willReturn($this->responseMock);

		$this->uriInterfaceMock->method('getPath')->willReturn('/api/resource');
		$this->requestMock->expects($this->never()) ->method('getAttribute');
		$this->handlerMock->expects($this->once())
					->method('handle')
					->with($this->requestMock)
					->willReturn($this->responseMock);


		$result = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertSame($this->responseMock, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testProcessReturnsControllerFalse(): void
	{
		$_ENV['APP_DEBUG'] = false;
		$this->handlerMock->method('handle')->willReturn($this->responseMock);
		$this->uriInterfaceMock->method('getPath')->willReturn('/resource');
		$layoutData = [];
		$this->requestMock->expects($this->once())
						  ->method('getAttribute')
						  ->with('layoutData', [])
						  ->willReturn($layoutData);

		$responseBodyMock = $this->createMock(StreamInterface::class);
		$this->responseMock->method('getBody')->willReturn($responseBodyMock);
		$this->responseMock->method('withHeader')->with('Content-Type', 'text/html')->willReturn($this->responseMock);
		$this->templateServiceMock->expects($this->never())->method('render');

		$result = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testProcessReturnsHtmlWithControllerData(): void
	{
		$_ENV['APP_DEBUG'] = false;
		$this->handlerMock->method('handle')->willReturn($this->responseMock);
		$this->uriInterfaceMock->method('getPath')->willReturn('/resource');
		$layoutData = [];
		$this->requestMock->expects($this->once())
						  ->method('getAttribute')
						  ->with('layoutData', [])
						  ->willReturn($layoutData);


		$this->requestMock->expects($this->once())
						  ->method('getAttribute')
						  ->with('layoutData', [])
						  ->willReturn([]);

		$controllerData = serialize([
			'this_layout' => [
				'template' => 'content',
				'data' => ['key' => 'value']
			],
			'main_layout' => ['title' => 'Test Title']
		]);
		$responseBodyMock = $this->createMock(StreamInterface::class);
		$responseBodyMock->method('__toString')->willReturn($controllerData);
		$this->responseMock->method('getBody')->willReturn($responseBodyMock);

		$this->templateServiceMock->expects($this->exactly(2))->method('render')
		  ->willReturnOnConsecutiveCalls('Rendered Content','Final Rendered Page');

		$this->responseMock->expects($this->once())->method('withBody')->willReturn($this->responseMock);

		$responseBodyMock->expects($this->once())->method('write')
						->with('Final Rendered Page')
		;
		$this->responseMock->method('withHeader')->with('Content-Type', 'text/html')->willReturn($this->responseMock);

		$result = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testProcessHandlesDebugMode(): void
	{
		$_ENV['APP_DEBUG'] = true;

		$this->handlerMock->method('handle')->willReturn($this->responseMock);
		$this->uriInterfaceMock->method('getPath')->willReturn('/resource');
		$layoutData = [];
		$this->requestMock->expects($this->exactly(3))->method('getAttribute');

		$responseBodyMock = $this->createMock(StreamInterface::class);
		$this->responseMock->method('getBody')->willReturn($responseBodyMock);
		$this->responseMock->method('withHeader')->with('Content-Type', 'text/html')->willReturn($this->responseMock);
		$this->templateServiceMock->expects($this->never())->method('render');

		$result = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $result);

	}
}

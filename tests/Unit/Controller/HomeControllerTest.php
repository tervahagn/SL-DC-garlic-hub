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
use App\Framework\Core\Locales\Locales;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use SlimSession\Helper;

class HomeControllerTest extends TestCase
{
	private ServerRequestInterface $requestMock;
	private ResponseInterface $responseMock;
	private Helper $sessionMock;
	private Locales $localesMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->requestMock  = $this->createMock(ServerRequestInterface::class);
		$this->responseMock = $this->createMock(ResponseInterface::class);
		$this->sessionMock  = $this->createMock(Helper::class);
		$this->localesMock  = $this->createMock(Locales::class);
	}

	#[Group('units')]
	public function testIndexRedirectsToLoginIfUserNotInSession(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('exists')->with('user')->willReturn(false);
		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', '/login')->willReturnSelf();
		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturnSelf();

		$controller = new HomeController();
		$result = $controller->index($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testIndexReturnsHomePageIfUserInSession(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('exists')->with('user')->willReturn(true);
		$this->sessionMock->method('get')->with('user')->willReturn(['username' => 'testuser']);
		$this->responseMock->method('getBody')->willReturn($this->createMock(StreamInterface::class));
		$this->responseMock->expects($this->once())->method('withHeader')->with('Content-Type', 'text/html')->willReturnSelf();

		$controller = new HomeController();
		$result = $controller->index($this->requestMock, $this->responseMock);

		$this->assertSame($this->responseMock, $result);
	}

	#[Group('units')]
	public function testSetLocales()
	{
		$this->requestMock->method('getAttribute')
			->willReturnCallback(function ($attribute) {
				return match ($attribute) {
					'session' => $this->sessionMock,
					'locales' => $this->localesMock,
					default => null,
				};
			});
		$this->sessionMock->method('get')->with('user')->willReturn(['locale' => 'en_US']);
		$this->sessionMock->expects($this->once())->method('set')->with('locale', 'de_DE');

		$this->localesMock->expects($this->once())->method('determineCurrentLocale');

		$previousUrl = 'some/url/line';
		$this->requestMock->method('getHeaderLine')->with('Referer')->willReturn($previousUrl);

		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', $previousUrl)
			->willReturn($this->responseMock);

		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturn($this->responseMock);

		$controller = new HomeController();
		$result = $controller->setLocales($this->requestMock, $this->responseMock, ['locale' => 'de_DE']);
		$this->assertSame($this->responseMock, $result);
	}

	#[Group('units')]
	public function testSetLocalesWithBrokenUSerArray()
	{
		$this->requestMock->method('getAttribute')
			->willReturnCallback(function ($attribute) {
				return match ($attribute) {
					'session' => $this->sessionMock,
					'locales' => $this->localesMock,
					default => null,
				};
			});
		$this->sessionMock->method('get')->with('user')->willReturn('not_an_array');
		$this->sessionMock->expects($this->once())->method('set')->with('locale', 'de_DE');

		$this->localesMock->expects($this->once())->method('determineCurrentLocale');

		$previousUrl = 'some/url/line';
		$this->requestMock->method('getHeaderLine')->with('Referer')->willReturn($previousUrl);

		$this->responseMock->expects($this->once())->method('withHeader')->with('Location', $previousUrl)
			->willReturn($this->responseMock);

		$this->responseMock->expects($this->once())->method('withStatus')->with(302)->willReturn($this->responseMock);

		$controller = new HomeController();
		$result = $controller->setLocales($this->requestMock, $this->responseMock, ['locale' => 'de_DE']);
		$this->assertSame($this->responseMock, $result);
	}


}

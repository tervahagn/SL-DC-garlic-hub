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

use App\Framework\Core\Config\Config;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Middleware\FinalRenderMiddleware;
use App\Framework\TemplateEngine\AdapterInterface;
use App\Modules\Users\Services\AclValidator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Flash\Messages;

class FinalRenderMiddlewareTest extends TestCase
{
	private readonly FinalRenderMiddleware $middleware;
	private readonly AclValidator $aclValidatorMock;
	private readonly AdapterInterface $templateServiceMock;
	private readonly ServerRequestInterface $requestMock ;
	private readonly ResponseInterface $responseMock;
	private readonly RequestHandlerInterface $handlerMock;
	private readonly UriInterface $uriInterfaceMock;
	private Translator $translatorMock;
	private Session $sessionMock;
	private Config $configMock;
	private Locales $localesMock;
	private Messages $flashMock;

	/**
	 * @throws \Exception|Exception
	 */
	protected function setUp(): void
	{
		$this->templateServiceMock = $this->createMock(AdapterInterface::class);
		$this->aclValidatorMock    = $this->createMock(AclValidator::class);
		$this->requestMock         = $this->createMock(ServerRequestInterface::class);
		$this->responseMock        = $this->createMock(ResponseInterface::class);
		$this->handlerMock         = $this->createMock(RequestHandlerInterface::class);
		$this->uriInterfaceMock    = $this->createMock(UriInterface::class);
		$this->translatorMock      = $this->createMock(Translator::class);
		$this->sessionMock         = $this->createMock(Session::class);
		$this->localesMock         = $this->createMock(Locales::class);
		$this->configMock          = $this->createMock(Config::class);
		$this->flashMock 	       = $this->createMock(Messages::class);
		$this->requestMock->method('getUri')->willReturn($this->uriInterfaceMock);

		$this->middleware = new FinalRenderMiddleware($this->templateServiceMock, $this->aclValidatorMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testProcessReturnsControllerFalse(): void
	{
		$_ENV['APP_DEBUG'] = false;
		$this->executeStandardMocks();

		// no need for layoutData here

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
		$this->executeStandardMocks();

		// no need for layoutData here

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

		$this->executeStandardMocks();

		// no need for layoutData here

		$responseBodyMock = $this->createMock(StreamInterface::class);
		$this->responseMock->method('getBody')->willReturn($responseBodyMock);
		$this->responseMock->method('withHeader')->with('Content-Type', 'text/html')->willReturn($this->responseMock);
		$this->templateServiceMock->expects($this->never())->method('render');

		$result = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $result);
	}

	#[Group('units')]
	public function testProcessWithMessage(): void
	{
		$_ENV['APP_DEBUG'] = false;
		$this->executeStandardMocks(true);

		$this->flashMock->expects($this->exactly(2))->method('getMessage')
			->willReturnCallback(function ($param)
			{
				return match ($param)
				{
					'error' => ['Error message'],
					'success' => ['Success message'],
					default => null,
				};
			});

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

		$responseBodyMock->expects($this->once())->method('write')	->with('Final Rendered Page');
		$this->responseMock->method('withHeader')->with('Content-Type', 'text/html')->willReturn($this->responseMock);
		$result = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $result);

	}

	#[Group('units')]
	public function testProcessWithUser(): void
	{
		$_ENV['APP_DEBUG'] = false;
		$this->executeStandardMocks(false, true);

		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 8]);
		$this->aclValidatorMock->method('isModuleAdmin')->with(8)->willReturn(false);
		$this->aclValidatorMock->method('isSubAdmin')->with(8)->willReturn(true);
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

		$responseBodyMock->expects($this->once())->method('write')	->with('Final Rendered Page');
		$this->responseMock->method('withHeader')->with('Content-Type', 'text/html')->willReturn($this->responseMock);
		$result = $this->middleware->process($this->requestMock, $this->handlerMock);

		$this->assertInstanceOf(ResponseInterface::class, $result);

	}


	private function collectLayoutData(): array
	{
		return [
			'messages' => [],
			'main_menu' => [['URL' => '/login', 'LANG_MENU_POINT' => 'translatedummy']],
			'CURRENT_LOCALE_LOWER' => 'en',
			'CURRENT_LOCALE_UPPER' => 'EN',
			'language_select' => [],
			'user_menu' => [],
			'APP_NAME' => 'edge',
			'LANG_LEGAL_NOTICE' => 'translatedummy',
			'LANG_PRIVACY' => 'translatedummy',
			'LANG_TERMS' => 'translatedummy'
		];
	}

	private function executeStandardMocks($hasMessages = false, $hasUser = false)
	{
		$this->handlerMock->method('handle')->willReturn($this->responseMock);
		$this->requestMock
			->method('getAttribute')
			->willReturnCallback(function ($attribute)
			{
				if ($attribute === 'session')
					return $this->sessionMock;
				elseif ($attribute === 'translator')
					return $this->translatorMock;
				elseif ($attribute === 'locales')
					return $this->localesMock;
				elseif ($attribute === 'config')
					return $this->configMock;
				elseif ($attribute === 'flash')
					return $this->flashMock;

				return null;
			});
		$this->flashMock->method('hasMessage')->willReturn($hasMessages);
		$this->sessionMock->expects($this->any())->method('exists')->with('user')->willReturn($hasUser);
		$this->translatorMock->method('translate')->willReturn('translatedummy');
		$this->translatorMock->method('translateArrayForOptions')->with('languages', 'menu')->willReturn([['key' => 'value']]);
		$this->localesMock->method('getLanguageCode')->willReturn('en');
		$this->configMock->method('getEnv')->with('APP_NAME')->willReturn('edge');
	}


}

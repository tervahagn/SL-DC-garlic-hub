<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\CsrfToken;
use App\Framework\Core\Session;
use App\Modules\Playlists\Controller\WidgetsController;
use App\Modules\Playlists\Services\WidgetsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class WidgetsControllerTest extends TestCase
{
	private WidgetsService&MockObject $widgetsServiceMock;
	private ResponseInterface&MockObject $responseMock;
	private ServerRequestInterface&MockObject $requestMock;
	private Session&MockObject $sessionMock;
	private StreamInterface&MockObject $streamInterfaceMock;
	private CsrfToken&MockObject $csrfTokenMock;
	private WidgetsController $controller;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->requestMock           = $this->createMock(ServerRequestInterface::class);
		$this->responseMock          = $this->createMock(ResponseInterface::class);
		$this->streamInterfaceMock   = $this->createMock(StreamInterface::class);
		$this->widgetsServiceMock    = $this->createMock(WidgetsService::class);
		$this->sessionMock           = $this->createMock(Session::class);
		$this->csrfTokenMock         = $this->createMock(CsrfToken::class);

		$this->controller = new WidgetsController($this->widgetsServiceMock, $this->csrfTokenMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFetchWithInvalidItemId(): void
	{
		$this->widgetsServiceMock->expects($this->never())->method('setUID');

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$this->controller->fetch($this->requestMock, $this->responseMock, []);
	}

	#[Group('units')]
	public function testFetchWithWidgetLoadFailure(): void
	{
		$args = ['item_id' => 123];

		$this->setServiceUIDMocks();
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Widget load failed.']);
		$this->widgetsServiceMock->expects($this->once())->method('fetchWidgetByItemId')
			->willReturn([]);

		$this->controller->fetch($this->requestMock, $this->responseMock, $args);
	}

	#[Group('units')]
	public function testFetchWithSuccessfulData(): void
	{
		$args = ['item_id' => 123];
		$data = ['some', 'data'];

		$this->setServiceUIDMocks();

		$this->widgetsServiceMock->expects($this->once())->method('fetchWidgetByItemId')
			->willReturn($data);

		$this->mockJsonResponse(['success' => true, 'data' => $data]);
		$this->controller->fetch($this->requestMock, $this->responseMock, $args);
	}

	#[Group('units')]
	public function testSaveWithInvalidCsrf(): void
	{
		$this->widgetsServiceMock->expects($this->never())->method('setUID');
		$this->widgetsServiceMock->expects($this->never())->method('saveWidget');

		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(false);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'CSRF token mismatch.']);

		$this->controller->save($this->requestMock, $this->responseMock);
	}


	#[Group('units')]
	public function testSaveWithInvalidItemId(): void
	{
		$this->widgetsServiceMock->expects($this->never())->method('setUID');
		$this->widgetsServiceMock->expects($this->never())->method('saveWidget');

		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$this->controller->save($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testSaveWithWidgetSaveFailure(): void
	{
		$requestData = ['item_id' => 123];
		$errorMessage = 'Save failed';

		$this->setServiceUIDMocks();

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->widgetsServiceMock->method('saveWidget')->with(123, $requestData)->willReturn(false);
		$this->widgetsServiceMock->method('getErrorText')->willReturn($errorMessage);

		$this->mockJsonResponse(['success' => false, 'error_message' => $errorMessage]);

		$this->controller->save($this->requestMock, $this->responseMock);

	}

	#[Group('units')]
	public function testSaveWithSuccessfulSave(): void
	{
		$requestData = ['item_id' => 123];

		$this->setServiceUIDMocks();

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->widgetsServiceMock->method('saveWidget')->with(123, $requestData)->willReturn(true);

		$this->mockJsonResponse(['success' => true]);

		$this->controller->save($this->requestMock, $this->responseMock);

	}

	private function setServiceUIDMocks(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->widgetsServiceMock->expects($this->once())->method('setUID')->with(456);
	}

	/**
	 * @param array<string,mixed> $data
	 */
	private function mockJsonResponse(array $data): void
	{
		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->streamInterfaceMock->method('write')->with(json_encode($data));
		$this->responseMock->expects($this->once())->method('withHeader')
			->with('Content-Type', 'application/json')
			->willReturnSelf();
		$this->responseMock->method('withStatus')->with('200');
	}

}

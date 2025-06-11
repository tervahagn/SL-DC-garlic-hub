<?php

namespace Tests\Unit\Modules\Playlists\Controller;

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
	private readonly ResponseInterface&MockObject $responseMock;
	private readonly ServerRequestInterface&MockObject $requestMock;
	private readonly Session&MockObject $sessionMock;
	private readonly StreamInterface&MockObject $streamInterfaceMock;
	private WidgetsController $controller;

	protected function setUp(): void
	{
		$this->requestMock           = $this->createMock(ServerRequestInterface::class);
		$this->responseMock          = $this->createMock(ResponseInterface::class);
		$this->streamInterfaceMock   = $this->createMock(StreamInterface::class);
		$this->widgetsServiceMock    = $this->createMock(WidgetsService::class);
		$this->sessionMock           = $this->createMock(Session::class);

		$this->controller = new WidgetsController($this->widgetsServiceMock);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFetchWithInvalidItemId(): void
	{
		$this->widgetsServiceMock->expects($this->never())->method('setUID');

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$response = $this->controller->fetch($this->requestMock, $this->responseMock, []);

		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testFetchWithWidgetLoadFailure(): void
	{
		$args = ['item_id' => 123];

		$this->setServiceUIDMocks();
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Widget load failed.']);
		$this->widgetsServiceMock->expects($this->once())->method('fetchWidgetByItemId')
			->willReturn([]);

		$response = $this->controller->fetch($this->requestMock, $this->responseMock, $args);

		$this->assertInstanceOf(ResponseInterface::class, $response);

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
		$response = $this->controller->fetch($this->requestMock, $this->responseMock, $args);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}


	#[Group('units')]
	public function testSaveWithInvalidItemId(): void
	{
		$this->widgetsServiceMock->expects($this->never())->method('setUID');
		$this->widgetsServiceMock->expects($this->never())->method('saveWidget');

		$this->requestMock->method('getParsedBody')->willReturn([]);

		$this->mockJsonResponse(['success' => false, 'error_message' => 'Item ID not valid.']);

		$response = $this->controller->save($this->requestMock, $this->responseMock);

		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testSaveWithWidgetSaveFailure(): void
	{
		$requestData = ['item_id' => 123];
		$errorMessage = 'Save failed';

		$this->setServiceUIDMocks();

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->widgetsServiceMock->method('saveWidget')->with(123, $requestData)->willReturn(false);
		$this->widgetsServiceMock->method('getErrorText')->willReturn($errorMessage);

		$this->mockJsonResponse(['success' => false, 'error_message' => $errorMessage]);

		$response = $this->controller->save($this->requestMock, $this->responseMock);

		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testSaveWithSuccessfulSave(): void
	{
		$requestData = ['item_id' => 123];

		$this->setServiceUIDMocks();

		$this->requestMock->method('getParsedBody')->willReturn($requestData);
		$this->widgetsServiceMock->method('saveWidget')->with(123, $requestData)->willReturn(true);

		$this->mockJsonResponse(['success' => true]);

		$response = $this->controller->save($this->requestMock, $this->responseMock);

		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	private function setServiceUIDMocks(): void
	{
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->widgetsServiceMock->expects($this->once())->method('setUID')->with(456);
	}
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

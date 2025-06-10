<?php

namespace Tests\Unit\Modules\Player\Controller;

use App\Framework\Core\Sanitizer;
use App\Modules\Player\Controller\PlayerIndexController;
use App\Modules\Player\Helper\Index\IndexResponseHandler;
use App\Modules\Player\Services\PlayerIndexService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Stream;

class PlayerIndexControllerTest extends TestCase
{
	private readonly PlayerIndexService $playerIndexServiceMock;
	private readonly IndexResponseHandler $indexResponseHandler;
	private readonly Sanitizer $sanitizerMock;
	private readonly ResponseInterface $responseMock;
	private readonly ServerRequestInterface $requestMock;
	private PlayerIndexController $playerIndexController;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerIndexServiceMock = $this->createMock(PlayerIndexService::class);
		$this->indexResponseHandler   = $this->createMock(IndexResponseHandler::class);
		$this->sanitizerMock          = $this->createMock(Sanitizer::class);
		$this->requestMock            = $this->createMock(ServerRequestInterface::class);
		$this->responseMock           = $this->createMock(ResponseInterface::class);

		$this->playerIndexController = new PlayerIndexController($this->playerIndexServiceMock, $this->indexResponseHandler, $this->sanitizerMock);
	}

	#[Group('units')]
	public function testIndexHandlesMissingFilePath(): void
	{
		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn([
			'HTTP_USER_AGENT' => 'TestAgent',
			'HTTP_X_SIGNAGE_AGENT' => 'extra useragent',
			'SERVER_NAME' => 'extern'
		]);

		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('extra useragent', false)
			->willReturn('');
		$this->indexResponseHandler->expects($this->never())->method('init');

		$this->responseMock->method('withHeader')
			->with('Content-Type', 'application/smil+xml')
			->willReturnSelf();
		$this->responseMock->method('withStatus')
			->with(404)
			->willReturnSelf();

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testIndexHandlesLocalhostPlayer(): void
	{
		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn([
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'localhost'
		]);
		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', true)
			->willReturn('');

		$this->indexResponseHandler->expects($this->never())->method('init');

		$this->responseMock->method('withHeader')
			->with('Content-Type', 'application/smil+xml')
			->willReturnSelf();
		$this->responseMock->method('withStatus')
			->with(404)
			->willReturnSelf();

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testIndexHandlesDdevPlayer(): void
	{
		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn([
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'garlic-hub.ddev.site',
			'REQUEST_METHOD' => 'HEAD'
		]);
		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', true)
			->willReturn('');
		$this->indexResponseHandler->expects($this->never())->method('init');

		$this->responseMock->method('withHeader')
			->with('Content-Type', 'application/smil+xml')
			->willReturnSelf();
		$this->responseMock->method('withStatus')
			->with(404)
			->willReturnSelf();

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testSendHead(): void
	{
		$filePath = '/tmp/test.smil';
		$server = [
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'localhost',
			'REQUEST_METHOD' => 'HEAD'
		];

		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn($server);

		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', 'localhost')->willReturn($filePath);

		$this->indexResponseHandler->expects($this->once())->method('init')
			->with($server, $filePath);
		$this->indexResponseHandler->expects($this->once())->method('doHEAD')
			->with($this->responseMock)
			->willReturn($this->responseMock);
		$this->indexResponseHandler->expects($this->never())->method('doGET');

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertSame($response, $this->responseMock);
	}

	#[Group('units')]
	public function testSendGet(): void
	{
		$filePath = '/tmp/test.smil';
		$server = [
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'localhost',
			'REQUEST_METHOD' => 'GET'
		];

		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn($server);

		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', 'localhost')->willReturn($filePath);

		$this->indexResponseHandler->expects($this->once())->method('init')
			->with($server, $filePath);
		$this->indexResponseHandler->expects($this->never())->method('doHEAD');
		$this->indexResponseHandler->expects($this->once())->method('doGET')
			->with($this->responseMock)
			->willReturn($this->responseMock);

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertSame($response, $this->responseMock);
	}

}

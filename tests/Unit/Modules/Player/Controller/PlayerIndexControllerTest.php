<?php

namespace Tests\Unit\Modules\Player\Controller;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Modules\Player\Controller\PlayerIndexController;
use App\Modules\Player\Services\PlayerIndexService;
use App\Modules\Player\Services\PlayerService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Stream;

class PlayerIndexControllerTest extends TestCase
{
	private readonly PlayerIndexService $playerIndexServiceMock;
	private readonly Sanitizer $sanitizerMock;
	private readonly PlayerService $playerServiceMock;
	private readonly ResponseInterface $responseMock;
	private readonly ServerRequestInterface $requestMock;
	private readonly Session $sessionMock;
	private readonly StreamInterface $streamInterfaceMock;
	private PlayerIndexController $playerIndexController;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerIndexServiceMock = $this->createMock(PlayerIndexService::class);
		$this->sanitizerMock          = $this->createMock(Sanitizer::class);
		$this->playerServiceMock      = $this->createMock(PlayerService::class);
		$this->requestMock            = $this->createMock(ServerRequestInterface::class);
		$this->responseMock           = $this->createMock(ResponseInterface::class);
		$this->sessionMock            = $this->createMock(Session::class);

		$this->playerIndexController = new PlayerIndexController($this->playerIndexServiceMock, $this->sanitizerMock);
	}

	#[Group('units')]
	public function testIndexHandlesMissingFilePath(): void
	{
		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn([
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'localhost'
		]);

		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', 'localhost')->willReturn('');

		$this->responseMock->method('withHeader')
			->with('Content-Type', 'application/smil+xml')
			->willReturnSelf();
		$this->responseMock->method('withStatus')
			->with(404)
			->willReturnSelf();

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock, []);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testIndexReturnsBody(): void
	{
		$filePath = '/tmp/test.smil';

		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn([
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'external-server',
			'REQUEST_METHOD' => 'GET'
		]);
		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', false)
			->willReturn($filePath);

		$this->playerIndexServiceMock->method('getFileMTime')->willReturn(1234567890);
		$lastModified = gmdate('D, d M Y H:i:s', 1234567890) . ' GMT';

		$fileStreamMock = $this->createMock(Stream::class);
		$this->playerIndexServiceMock->method('createStream')->willReturn($fileStreamMock);
		$this->playerIndexServiceMock->method('getFilesize')->willReturn(1024);
		// return a smil body
		$this->responseMock->method('withBody')->with($fileStreamMock)->willReturnSelf();
		$this->responseMock->expects($this->exactly(6))->method('withHeader')
			->willReturnMap([
				['Cache-Control', 'public, must-revalidate, max-age=864000, pre-check=864000', $this->responseMock],
				['Last-Modified', $lastModified, $this->responseMock],
				['Content-Length', '1024', $this->responseMock],
				['Content-Type', 'application/smil+xml', $this->responseMock],
				['Content-Description', 'File Transfer', $this->responseMock],
				['Content-Disposition', 'attachment; filename="test.smil"', $this->responseMock]
			]);

		$this->responseMock->method('withStatus')->with(200);

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock, []);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testIndexReturnsHead(): void
	{
		$filePath = '/tmp/test.smil';

		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn([
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'external-server',
			'REQUEST_METHOD' => 'HEAD'
		]);
		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', false)
			->willReturn($filePath);

		$this->playerIndexServiceMock->method('getFileMTime')->willReturn(1234567890);
		$lastModified = gmdate('D, d M Y H:i:s', 1234567890) . ' GMT';

		$this->playerIndexServiceMock->expects($this->never())->method('createStream');
		$this->playerIndexServiceMock->expects($this->never())->method('getFilesize');
		// return a smil body
		$this->responseMock->expects($this->exactly(3))->method('withHeader')
			->willReturnMap([
				['Cache-Control', 'public, must-revalidate, max-age=864000, pre-check=864000', $this->responseMock],
				['Content-Type', 'application/smil+xml', $this->responseMock],
				['Last-Modified', $lastModified, $this->responseMock],
		]);
		$this->responseMock->method('withStatus')->with(200);

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock, []);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testIndexReturnsNothingChanged(): void
	{
		$filePath = '/tmp/test.smil';

		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn([
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'external-server',
			'REQUEST_METHOD' => 'HEAD',
			'HTTP_IF_MODIFIED_SINCE' => gmdate('D, d M Y H:i:s', 1234567891) . ' GMT'
		]);
		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', false)
			->willReturn($filePath);

		$this->playerIndexServiceMock->method('getFileMTime')->willReturn(1234567890);
		$lastModified = gmdate('D, d M Y H:i:s', 1234567890) . ' GMT';

		$this->playerIndexServiceMock->expects($this->never())->method('createStream');
		$this->playerIndexServiceMock->expects($this->never())->method('getFilesize');
		// return a smil body
		$this->responseMock->expects($this->exactly(2))->method('withHeader')
			->willReturnMap([
				['Cache-Control', 'public, must-revalidate, max-age=864000, pre-check=864000', $this->responseMock],
				['Last-Modified', $lastModified, $this->responseMock],
			]);
		$this->responseMock->method('withStatus')->with(304);

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock, []);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testIndexHandlesLocalPlayer(): void
	{
		$filePath = '/tmp/test.smil';

		$this->requestMock->method('getQueryParams')->willReturn([]);
		$this->requestMock->method('getServerParams')->willReturn([
			'HTTP_USER_AGENT' => 'TestAgent',
			'SERVER_NAME' => 'localhost',
			'REQUEST_METHOD' => 'HEAD'
		]);
		$this->sanitizerMock->method('int')->with(0)->willReturn(0);
		$this->playerIndexServiceMock->method('setUID')->with(0);
		$this->playerIndexServiceMock->method('handleIndexRequest')
			->with('TestAgent', true)
			->willReturn($filePath);

		$this->playerIndexServiceMock->method('getFileMTime')->willReturn(1234567890);
		$lastModified = gmdate('D, d M Y H:i:s', 1234567890) . ' GMT';

		$this->playerIndexServiceMock->expects($this->never())->method('createStream');
		$this->playerIndexServiceMock->expects($this->never())->method('getFilesize');
		// return a smil body
		$this->responseMock->expects($this->exactly(3))->method('withHeader')
			->willReturnMap([
				['Cache-Control', 'public, must-revalidate, max-age=864000, pre-check=864000', $this->responseMock],
				['Content-Type', 'application/smil+xml', $this->responseMock],
				['Last-Modified', $lastModified, $this->responseMock],
			]);
		$this->responseMock->method('withStatus')->with(200);

		$response = $this->playerIndexController->index($this->requestMock, $this->responseMock, []);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}
}

<?php

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Controller\ExportController;
use App\Modules\Playlists\Services\ExportService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ExportControllerTest extends TestCase
{
	private ExportController $exportController;
	private ExportService&MockObject $exportServiceMock;
	private ResponseInterface&MockObject $responseMock;
	private ServerRequestInterface&MockObject $requestMock;
	private Session&MockObject $sessionMock;
	private StreamInterface&MockObject $streamInterfaceMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->exportServiceMock = $this->createMock(ExportService::class);
		$this->requestMock = $this->createMock(ServerRequestInterface::class);
		$this->responseMock = $this->createMock(ResponseInterface::class);
		$this->sessionMock = $this->createMock(Session::class);
		$this->streamInterfaceMock = $this->createMock(StreamInterface::class);

		$this->exportController = new ExportController($this->exportServiceMock);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testExport(): void
	{
		$post = ['playlist_id' => 69];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->exportServiceMock->method('setUID')->with(456);

		$this->exportServiceMock->method('exportToSmil')->with(69)->willReturn(1);
		$this->mockJsonResponse(['success' => true]);

		$response = $this->exportController->export($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testExportWithInvalidPlaylistId(): void
	{
		$post = [];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->requestMock->expects($this->never())->method('getAttribute');

		$this->exportServiceMock->expects($this->never())->method('exportToSmil');
		$this->mockJsonResponse(['success' => false, 'error_message' =>  'Playlist ID not valid.']);

		$response = $this->exportController->export($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testExportWhenPlaylistNotFound(): void
	{
		$post = ['playlist_id' => 69];
		$this->requestMock->method('getParsedBody')->willReturn($post);

		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 456]);
		$this->exportServiceMock->method('setUID')->with(456);

		$this->exportServiceMock->method('exportToSmil')->with(69)->willReturn(0);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist not found.']);

		$response = $this->exportController->export($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
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

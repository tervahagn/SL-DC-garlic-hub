<?php

namespace Tests\Unit\Modules\Playlists\Controller;

use App\Framework\Core\CsrfToken;
use App\Modules\Auth\UserSession;
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
	private UserSession&MockObject $userSessionMock;
	private CsrfToken&MockObject $csrfTokenMock;
	private StreamInterface&MockObject $streamInterfaceMock;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->exportServiceMock = $this->createMock(ExportService::class);
		$this->requestMock = $this->createMock(ServerRequestInterface::class);
		$this->responseMock = $this->createMock(ResponseInterface::class);
		$this->userSessionMock = $this->createMock(UserSession::class);
		$this->streamInterfaceMock = $this->createMock(StreamInterface::class);
		$this->csrfTokenMock    = $this->createMock(CsrfToken::class);

		$this->exportController = new ExportController($this->exportServiceMock, $this->userSessionMock, $this->csrfTokenMock);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testExport(): void
	{
		$post = ['playlist_id' => 69];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->userSessionMock->method('getUID')->willReturn(456);
		$this->exportServiceMock->method('setUID')->with(456);

		$this->exportServiceMock->method('exportToSmil')->with(69)->willReturn(1);
		$this->mockJsonResponse(['success' => true]);

		$this->exportController->export($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testExportWithInvalidPlaylistId(): void
	{
		$post = [];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->exportServiceMock->expects($this->never())->method('exportToSmil');
		$this->mockJsonResponse(['success' => false, 'error_message' =>  'Playlist ID not valid.']);

		$this->exportController->export($this->requestMock, $this->responseMock);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testExportWhenPlaylistNotFound(): void
	{
		$post = ['playlist_id' => 69];
		$this->requestMock->method('getParsedBody')->willReturn($post);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->userSessionMock->method('getUID')->willReturn(456);
		$this->exportServiceMock->method('setUID')->with(456);

		$this->exportServiceMock->method('exportToSmil')->with(69)->willReturn(0);
		$this->mockJsonResponse(['success' => false, 'error_message' => 'Playlist not found.']);

		$this->exportController->export($this->requestMock, $this->responseMock);
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

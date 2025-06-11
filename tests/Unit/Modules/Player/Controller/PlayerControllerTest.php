<?php

namespace Tests\Unit\Modules\Player\Controller;

use App\Framework\Core\Session;
use App\Modules\Player\Controller\PlayerController;
use App\Modules\Player\Services\PlayerService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class PlayerControllerTest extends TestCase
{
	private PlayerService&MockObject $playerServiceMock;
	private ResponseInterface&MockObject $responseMock;
	private ServerRequestInterface&MockObject $requestMock;
	private Session&MockObject $sessionMock;
	private StreamInterface&MockObject $streamInterfaceMock;
	private PlayerController $playerController;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerServiceMock = $this->createMock(PlayerService::class);
		$this->requestMock  = $this->createMock(ServerRequestInterface::class);
		$this->responseMock = $this->createMock(ResponseInterface::class);
		$this->sessionMock  = $this->createMock(Session::class);
		$this->streamInterfaceMock = $this->createMock(StreamInterface::class);

		$this->playerController = new PlayerController($this->playerServiceMock);
	}

	#[Group('units')]
	public function testReplacePlaylistWithInvalidPlayerId(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->mockJsonResponse(['success' => false, 'error_message' =>  'Player ID not valid.']);

		$response = $this->playerController->replacePlaylist($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testReplacePlaylistWithInvalidPlaylistId(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn(['player_id' => 1, 'playlist_id' => null]);

		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 123]);

		$this->playerServiceMock->expects($this->once())->method('setUID')->with(123);
		$this->playerServiceMock->expects($this->once())->method('replaceMasterPlaylist')
			->with(1, 0)
			->willReturn([]);

		$this->playerServiceMock->method('getErrorMessages')->willReturn(['Error message']);

		$this->mockJsonResponse(['success' => false, 'error_message' =>  ['Error message']]);

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$response = $this->playerController->replacePlaylist($this->requestMock, $this->responseMock);
		$this->assertInstanceOf(ResponseInterface::class, $response);
	}

	#[Group('units')]
	public function testReplacePlaylistSuccessfully(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn(['player_id' => 3, 'playlist_id' => 5]);

		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 789]);

		$this->playerServiceMock->expects($this->once())->method('setUID')->with(789);
		$this->playerServiceMock->expects($this->once())->method('replaceMasterPlaylist')
			->with(3, 5)
			->willReturn(['affected' => 1, 'playlist_name' => 'Playlist Name']);

		$this->mockJsonResponse(['success' => true, 'playlist_name' => 'Playlist Name']);


		$response = $this->playerController->replacePlaylist($this->requestMock, $this->responseMock);
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

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


namespace Tests\Unit\Modules\Player\Controller;

use App\Framework\Core\CsrfToken;
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
	private CsrfToken&MockObject $csrfTokenMock;
	private PlayerController $playerController;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->playerServiceMock = $this->createMock(PlayerService::class);
		$this->requestMock  = $this->createMock(ServerRequestInterface::class);
		$this->responseMock = $this->createMock(ResponseInterface::class);
		$this->sessionMock  = $this->createMock(Session::class);
		$this->csrfTokenMock    = $this->createMock(CsrfToken::class);
		$this->streamInterfaceMock = $this->createMock(StreamInterface::class);

		$this->playerController = new PlayerController($this->playerServiceMock, $this->csrfTokenMock);
	}

	#[Group('units')]
	public function testReplacePlaylistWithInvalidPlayerId(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn([]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);
		$this->mockJsonResponse(['success' => false, 'error_message' =>  'Player ID not valid.']);

		$this->playerController->replacePlaylist($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testReplacePlaylistWithInvalidPlaylistId(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn(['player_id' => 1, 'playlist_id' => null]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 123]);

		$this->playerServiceMock->expects($this->once())->method('setUID')->with(123);
		$this->playerServiceMock->expects($this->once())->method('replaceMasterPlaylist')
			->with(1, 0)
			->willReturn([]);

		$this->playerServiceMock->method('getErrorMessages')->willReturn(['Error message']);

		$this->mockJsonResponse(['success' => false, 'error_message' =>  ['Error message']]);

		$this->responseMock->method('getBody')->willReturn($this->streamInterfaceMock);
		$this->playerController->replacePlaylist($this->requestMock, $this->responseMock);
	}

	#[Group('units')]
	public function testReplacePlaylistSuccessfully(): void
	{
		$this->requestMock->method('getParsedBody')->willReturn(['player_id' => 3, 'playlist_id' => 5]);
		$this->csrfTokenMock->expects($this->once())->method('validateToken')->willReturn(true);

		$this->requestMock->method('getAttribute')->with('session')->willReturn($this->sessionMock);
		$this->sessionMock->method('get')->with('user')->willReturn(['UID' => 789]);

		$this->playerServiceMock->expects($this->once())->method('setUID')->with(789);
		$this->playerServiceMock->expects($this->once())->method('replaceMasterPlaylist')
			->with(3, 5)
			->willReturn(['affected' => 1, 'playlist_name' => 'Playlist Name']);

		$this->mockJsonResponse(['success' => true, 'playlist_name' => 'Playlist Name']);

		$this->playerController->replacePlaylist($this->requestMock, $this->responseMock);
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

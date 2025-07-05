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

namespace Tests\Unit\Modules\Player\Services;

use App\Modules\Player\Repositories\PlayerRepository;
use App\Modules\Player\Services\AclValidator;
use App\Modules\Player\Services\PlayerService;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Services\PlaylistsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PlayerServiceTest extends TestCase
{
	private PlayerRepository&MockObject $playerRepositoryMock;
	private PlaylistsService&MockObject $playlistServiceMock;
	private AclValidator&MockObject $playerValidatorMock;
	private LoggerInterface&MockObject $loggerMock;
	private PlayerService $service;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->playerRepositoryMock = $this->createMock(PlayerRepository::class);
		$this->playlistServiceMock  = $this->createMock(PlaylistsService::class);
		$this->playerValidatorMock  = $this->createMock(AclValidator::class);
		$this->loggerMock           = $this->createMock(LoggerInterface::class);

		$this->service = new PlayerService($this->playerRepositoryMock, $this->playlistServiceMock, $this->playerValidatorMock, $this->loggerMock);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllForDashboardReturnsValidData(): void
	{
		$this->playerRepositoryMock->method('findAllForDashboard')
			->willReturn(['active' => 5, 'inactive' => 3, 'pending' => 2]);

		$result = $this->service->findAllForDashboard();

		static::assertSame(['active' => 5, 'inactive' => 3, 'pending' => 2], $result);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllForDashboardReturnsDefaultValuesOnEmptyData(): void
	{
		$this->playerRepositoryMock->method('findAllForDashboard')
			->willReturn([]);

		$result = $this->service->findAllForDashboard();

		static::assertSame(['active' => 0, 'inactive' => 0, 'pending' => 0], $result);
	}

	#[Group('units')]
	public function testReplaceMasterPlaylist(): void
	{
		$this->service->setUID(1);
		$this->playerRepositoryMock->method('findFirstById')
			->willReturn(['id' => 1, 'name' => 'Player 1']);

		$this->playerValidatorMock->method('isPlayerEditable')
			->willReturn(true);

		$this->playlistServiceMock->method('loadPureById')
			->willReturn(['playlist_mode' => PlaylistMode::MASTER->value, 'playlist_name' => 'Master Playlist']);

		$this->playerRepositoryMock->method('update')
			->willReturn(1);

		$result = $this->service->replaceMasterPlaylist(1, 10);

		static::assertSame(['affected' => 1, 'playlist_name' => 'Master Playlist'], $result);
	}

	#[Group('units')]
	public function testReplaceMasterPlaylistWithInvalidMasterPlaylistMode(): void
	{
		$this->service->setUID(1);
		$this->playerRepositoryMock->method('findFirstById')
			->willReturn(['id' => 1, 'name' => 'Player 1']);

		$this->playerValidatorMock->method('isPlayerEditable')
			->willReturn(true);

		$this->playlistServiceMock->method('loadPureById')
			->willReturn(['playlist_mode' => PlaylistMode::CHANNEL->value, 'playlist_name' => 'Channel Playlist']);

		$this->loggerMock->expects($this->once())
			->method('error')
			->with('Channel Playlist is not a master playlist');

		$result = $this->service->replaceMasterPlaylist(1, 10);

		static::assertSame([], $result);
	}

	#[Group('units')]
	public function testReplaceMasterPlaylistWithInvalidPlayerId(): void
	{
		$this->service->setUID(1);
		$this->playerRepositoryMock->method('findFirstById')
			->willReturn([]);

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error loading player: Is not editable');

		$this->playerValidatorMock->method('isPlayerEditable')
			->willReturn(false);

		$result = $this->service->replaceMasterPlaylist(999, 10);

		static::assertSame([], $result);
	}

	#[Group('units')]
	public function testReplaceMasterPlaylistWithoutPlaylistId(): void
	{
		$this->service->setUID(1);
		$this->playerRepositoryMock->method('findFirstById')
			->willReturn(['id' => 1, 'name' => 'Player 1']);

		$this->playerValidatorMock->method('isPlayerEditable')
			->willReturn(true);

		$this->playerRepositoryMock->method('update')
			->willReturn(1);

		$result = $this->service->replaceMasterPlaylist(1, 0);

		static::assertSame(['affected' => 1, 'playlist_name' => ''], $result);
	}


}

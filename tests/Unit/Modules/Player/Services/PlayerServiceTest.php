<?php

namespace Tests\Unit\Modules\Player\Services;

use App\Modules\Player\Repositories\PlayerRepository;
use App\Modules\Player\Services\AclValidator;
use App\Modules\Player\Services\PlayerService;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Services\PlaylistsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PlayerServiceTest extends TestCase
{
	private readonly PlayerRepository $playerRepositoryMock;
	private readonly PlaylistsService $playlistServiceMock;
	private readonly AclValidator $playerValidatorMock;
	private readonly LoggerInterface $loggerMock;
	private PlayerService $service;

	protected function setUp(): void
	{
		$this->playerRepositoryMock = $this->createMock(PlayerRepository::class);
		$this->playlistServiceMock  = $this->createMock(PlaylistsService::class);
		$this->playerValidatorMock  = $this->createMock(AclValidator::class);
		$this->loggerMock           = $this->createMock(LoggerInterface::class);

		$this->service = new PlayerService($this->playerRepositoryMock, $this->playlistServiceMock, $this->playerValidatorMock, $this->loggerMock);
	}

	#[Group('units')]
	public function testReplaceMasterPlaylis(): void
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

		$this->assertSame(['affected' => 1, 'playlist_name' => 'Master Playlist'], $result);
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

		$this->assertSame([], $result);
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

		$this->assertSame([], $result);
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

		$this->assertSame(['affected' => 1, 'playlist_name' => ''], $result);
	}


}

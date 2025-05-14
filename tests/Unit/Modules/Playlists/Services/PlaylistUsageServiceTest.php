<?php

namespace Tests\Unit\Modules\Playlists\Services;

use App\Modules\Player\Repositories\PlayerRepository;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Services\PlaylistUsageService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class PlaylistUsageServiceTest extends TestCase
{
	private readonly PlayerRepository $playerRepositoryMock;
	private readonly ItemsRepository $itemsRepositoryMock;
	private readonly PlaylistUsageService $service;

	protected function setUp(): void
	{
		$this->playerRepositoryMock = $this->createMock(PlayerRepository::class);
		$this->itemsRepositoryMock  = $this->createMock(ItemsRepository::class);

		$this->service = new PlaylistUsageService($this->playerRepositoryMock, $this->itemsRepositoryMock);
	}

	#[Group('units')]
	public function testDeterminePlaylistsInUseWithValidResults(): void
	{
		$playlistIds = [1, 2, 3];

		$this->playerRepositoryMock->method('findPlaylistIdsByPlaylistIds')
			->with($playlistIds)
			->willReturnMap([
				[$playlistIds, [['playlist_id' => 1], ['playlist_id' => 2]]]
			]);

		$this->itemsRepositoryMock->method('findFileResourcesByPlaylistId')
			->with($playlistIds)
			->willReturnMap([
				[$playlistIds, [['playlist_id' => 2], ['playlist_id' => 3]]]
			]);

		$result = $this->service->determinePlaylistsInUse($playlistIds);

		$this->assertSame([
			1 => true,
			2 => true,
			3 => true
		], $result);
	}

	#[Group('units')]
	public function testDeterminePlaylistsInUseWithNoResults(): void
	{
		$playlistIds = [1, 2, 3];

		$this->playerRepositoryMock->method('findPlaylistIdsByPlaylistIds')
			->with($playlistIds)
			->willReturnMap([
				[$playlistIds, []]
			]);

		$this->itemsRepositoryMock->method('findFileResourcesByPlaylistId')
			->with($playlistIds)
			->willReturnMap([
				[$playlistIds, []]
			]);

		$result = $this->service->determinePlaylistsInUse($playlistIds);

		$this->assertSame([], $result);
	}

}

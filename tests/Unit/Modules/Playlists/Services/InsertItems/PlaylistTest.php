<?php

namespace Tests\Unit\Modules\Playlists\Services\InsertItems;

use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Helper\ItemType;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Services\InsertItems\Playlist;
use App\Modules\Playlists\Services\PlaylistMetricsCalculator;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PlaylistTest extends TestCase
{
	private ItemsRepository&MockObject $itemsRepositoryMock;
	private PlaylistsService&MockObject $playlistsServiceMock;
	private PlaylistMetricsCalculator&MockObject $playlistMetricsCalculatorMock;
	private LoggerInterface&MockObject $loggerMock;
	private MediaService&MockObject $mediaServiceMock;
	private Playlist $playlist;

	protected function setUp(): void
	{
		$this->mediaServiceMock = $this->createMock(MediaService::class);
		$this->itemsRepositoryMock = $this->createMock(ItemsRepository::class);
		$this->playlistsServiceMock = $this->createMock(PlaylistsService::class);
		$this->playlistMetricsCalculatorMock = $this->createMock(PlaylistMetricsCalculator::class);
		$this->loggerMock = $this->createMock(LoggerInterface::class);

		$this->playlist = new Playlist(
			$this->itemsRepositoryMock,
			$this->playlistsServiceMock,
			$this->playlistMetricsCalculatorMock,
			$this->loggerMock
		);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testInsertSuccessful(): void
	{
		$playlistId = 1;
		$insertId = 2;
		$position = 1;
		$playlistTargetData = ['playlist_id' => 1, 'duration' => 300, 'filesize' => 2048, 'playlist_name' => 'Test'];
		$playlistInsertData = ['playlist_id' => 2, 'duration' => 150, 'filesize' => 1024, 'playlist_name' => 'Insert'];
		$playlistMetrics = ['some_metrics'];

		$this->playlist->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('beginTransaction');
		$this->checkAclMockSuccessful($playlistTargetData, $playlistInsertData);

		$this->itemsRepositoryMock->method('findAllPlaylistItemsByPlaylistId')
			->with($insertId)
			->willReturn([]);

		$this->itemsRepositoryMock->method('updatePositionsWhenInserted')
			->with($playlistId)
			->willReturn(1);

		$saveItem = [
			'playlist_id'   => $playlistId,
			'datasource'    => 'file',
			'UID'           => 1,
			'item_duration' => $playlistInsertData['duration'],
			'item_filesize' => $playlistInsertData['filesize'],
			'item_order'    => $position,
			'item_name'     => $playlistInsertData['playlist_name'],
			'item_type'     => ItemType::PLAYLIST->value,
			'file_resource' => $insertId,
			'mimetype'      => ''
		];

		$this->itemsRepositoryMock->method('insert')
			->with($saveItem)
			->willReturn(1);

		$this->playlistMetricsCalculatorMock
			->method('calculateFromPlaylistData')
			->willReturnSelf();
		$this->playlistMetricsCalculatorMock
			->method('getMetricsForFrontend')
			->willReturn($playlistMetrics);

		$this->itemsRepositoryMock->method('commitTransaction');

		$result = $this->playlist->insert($playlistId, $insertId, $position);

		$saveItem['item_id'] = 1;
		$saveItem['paths']['thumbnail'] = 'public/images/icons/playlist.svg';

		$this->assertSame($playlistMetrics, $result['playlist_metrics']);
		$this->assertSame($saveItem, $result['item']);
	}

	#[Group('units')]
	public function testInsertCheRecursionFailsSamePlaylist(): void
	{
		$playlistId = 1;
		$insertId = 1;
		$position = 1;
		$playlistTargetData = ['playlist_id' => 1, 'duration' => 300, 'filesize' => 2048, 'playlist_name' => 'Test'];
		$playlistInsertData = ['playlist_id' => 2, 'duration' => 150, 'filesize' => 1024, 'playlist_name' => 'Insert'];

		$this->playlist->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('beginTransaction');
		$this->checkAclMockSuccessful($playlistTargetData, $playlistInsertData);

		$this->itemsRepositoryMock->expects($this->never())->method('findAllPlaylistItemsByPlaylistId');

		$this->itemsRepositoryMock->expects($this->never())->method('updatePositionsWhenInserted');

		$this->itemsRepositoryMock->method('rollBackTransaction');
		$this->loggerMock->expects($this->once())->method('error')
			->with('Error insert playlist: Playlist recursion alert.');

		$this->assertEmpty($this->playlist->insert($playlistId, $insertId, $position));
	}

	#[Group('units')]
	public function testInsertCheRecursionFailsSamePlaylistAfterRecursion(): void
	{
		$playlistId = 1;
		$insertId = 2;
		$position = 1;
		$playlistTargetData = ['playlist_id' => 1, 'duration' => 300, 'filesize' => 2048, 'playlist_name' => 'Test'];
		$playlistInsertData = ['playlist_id' => 2, 'duration' => 150, 'filesize' => 1024, 'playlist_name' => 'Insert'];

		$this->playlist->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('beginTransaction');
		$this->checkAclMockSuccessful($playlistTargetData, $playlistInsertData);

		$this->itemsRepositoryMock->expects($this->once())->method('findAllPlaylistItemsByPlaylistId')
			->with($insertId)
			->willReturn([
				['file_resource' => $playlistId]
			]);

		$this->itemsRepositoryMock->expects($this->never())->method('updatePositionsWhenInserted');

		$this->itemsRepositoryMock->method('rollBackTransaction');
		$this->loggerMock->expects($this->once())->method('error')
			->with('Error insert playlist: Playlist recursion alert.');

		$this->assertEmpty($this->playlist->insert($playlistId, $insertId, $position));
	}

	#[Group('units')]
	public function testInsertFailsOnUpdatePosition(): void
	{
		$playlistId = 1;
		$insertId = 2;
		$position = 1;
		$playlistTargetData = ['playlist_id' => 1, 'duration' => 300, 'filesize' => 2048, 'playlist_name' => 'Test'];
		$playlistInsertData = ['playlist_id' => 2, 'duration' => 150, 'filesize' => 1024, 'playlist_name' => 'Insert'];

		$this->playlist->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('beginTransaction');
		$this->checkAclMockSuccessful($playlistTargetData, $playlistInsertData);

		$this->itemsRepositoryMock->method('findAllPlaylistItemsByPlaylistId')
			->with($insertId)
			->willReturn([]);

		$this->itemsRepositoryMock->method('updatePositionsWhenInserted')
			->with($playlistId)
			->willReturn(0);

		$this->itemsRepositoryMock->expects($this->never())->method('insert');

		$this->itemsRepositoryMock->method('rollBackTransaction');
		$this->loggerMock->expects($this->once())->method('error')
			->with('Error insert playlist: Positions could not be updated.');

		$this->assertEmpty($this->playlist->insert($playlistId, $insertId, $position));
	}

	#[Group('units')]
	public function testInsertFails(): void
	{
		$playlistId = 1;
		$insertId = 2;
		$position = 1;
		$playlistTargetData = ['playlist_id' => 1, 'duration' => 300, 'filesize' => 2048, 'playlist_name' => 'Test'];
		$playlistInsertData = ['playlist_id' => 2, 'duration' => 150, 'filesize' => 1024, 'playlist_name' => 'Insert'];
		$playlistMetrics = ['some_metrics'];

		$this->playlist->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('beginTransaction');
		$this->checkAclMockSuccessful($playlistTargetData, $playlistInsertData);

		$this->itemsRepositoryMock->method('findAllPlaylistItemsByPlaylistId')
			->with($insertId)
			->willReturn([]);

		$this->itemsRepositoryMock->method('updatePositionsWhenInserted')
			->with($playlistId)
			->willReturn(1);

		$saveItem = [
			'playlist_id'   => $playlistId,
			'datasource'    => 'file',
			'UID'           => 1,
			'item_duration' => $playlistInsertData['duration'],
			'item_filesize' => $playlistInsertData['filesize'],
			'item_order'    => $position,
			'item_name'     => $playlistInsertData['playlist_name'],
			'item_type'     => ItemType::PLAYLIST->value,
			'file_resource' => $insertId,
			'mimetype'      => ''
		];

		$this->itemsRepositoryMock->method('insert')
			->with($saveItem)
			->willReturn(0);

		$this->playlistMetricsCalculatorMock->expects($this->never())->method('calculateFromPlaylistData');

		$this->itemsRepositoryMock->method('rollBackTransaction');
		$this->loggerMock->expects($this->once())->method('error')
			->with('Error insert playlist: Playlist item could not inserted.');

		$this->assertEmpty($this->playlist->insert($playlistId, $insertId, $position));
	}

	private function checkAclMockSuccessful(array $playlistTargetData, array $playlistInsertData): void
	{
		$this->playlistsServiceMock->expects($this->exactly(2))->method('setUID')
			->with(1);
		$this->playlistsServiceMock->expects($this->exactly(2))->method('loadPlaylistForEdit')
			->willReturnMap([
				[$playlistTargetData['playlist_id'], $playlistTargetData],
				[$playlistInsertData['playlist_id'], $playlistInsertData]
			]);
	}
}

<?php

namespace Tests\Unit\Modules\Playlists\Services;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Helper\ItemType;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Services\ItemsService;
use App\Modules\Playlists\Services\PlaylistMetricsCalculator;
use App\Modules\Playlists\Services\PlaylistsService;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ItemsServiceTest extends TestCase
{
	private ItemsRepository&MockObject $itemsRepositoryMock;
	private PlaylistsService&MockObject $playlistsServiceMock;
	private MediaService&MockObject $mediaServiceMock;
	private PlaylistMetricsCalculator&MockObject $playlistMetricsCalculatorMock;
	private LoggerInterface&MockObject $loggerMock;
	private ItemsService $itemsService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->itemsRepositoryMock = $this->createMock(ItemsRepository::class);
		$this->playlistsServiceMock = $this->createMock(PlaylistsService::class);
		$this->mediaServiceMock = $this->createMock(MediaService::class);
		$this->playlistMetricsCalculatorMock = $this->createMock(PlaylistMetricsCalculator::class);
		$this->loggerMock = $this->createMock(LoggerInterface::class);

		$this->itemsService = new ItemsService(
			$this->itemsRepositoryMock,
			$this->mediaServiceMock,
			$this->playlistsServiceMock,
			$this->playlistMetricsCalculatorMock,
			$this->loggerMock
		);
	}

	#[Group('units')]
	public function testGetItemsRepository(): void
	{
		$result = $this->itemsService->getItemsRepository();
		$this->assertSame($this->itemsRepositoryMock, $result);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadByPlaylistForExportWithResults(): void
	{
		$playlist = ['playlist_id' => 1];
		$edition = Config::PLATFORM_EDITION_EDGE;

		$conditional = serialize(['conditions' => '']);
		$results = [
			['conditional' => $conditional, 'properties' => '', 'categories' => '', 'content_data' => '', 'begin_trigger' => '', 'end_trigger' => ''],
			['conditional' => '', 'properties' => '', 'categories' => '', 'content_data' => '', 'begin_trigger' => '', 'end_trigger' => ''],
		];

		$this->itemsRepositoryMock->expects($this->once())->method('findAllByPlaylistIdWithJoins')
			->with($playlist['playlist_id'], $edition)
			->willReturn($results);

		$metrics = ['metric1' => 'value1'];
		$this->playlistMetricsCalculatorMock->expects($this->once())->method('reset')
			->willReturnSelf();
		$this->playlistMetricsCalculatorMock->expects($this->once())->method('calculateFromItems')
			->with($playlist, $results)
			->willReturnSelf();
		$this->playlistMetricsCalculatorMock->expects($this->once())->method('getMetricsForPlaylistTable')
			->willReturn($metrics);

		$result = $this->itemsService->loadByPlaylistForExport($playlist, $edition);

		$this->assertArrayHasKey('playlist_metrics', $result);
		$this->assertArrayHasKey('items', $result);

		$this->assertEquals($metrics, $result['playlist_metrics']);
		$this->assertCount(2, $result['items']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadByPlaylistForExportWithEmptyResults(): void
	{
		$playlist = ['playlist_id' => 1];
		$edition = Config::PLATFORM_EDITION_EDGE;

		$this->itemsRepositoryMock->expects($this->once())->method('findAllByPlaylistIdWithJoins')
			->with($playlist['playlist_id'], $edition)
			->willReturn([]);

		$this->playlistMetricsCalculatorMock->expects($this->once())->method('reset')
			->willReturnSelf();

		$this->playlistMetricsCalculatorMock->expects($this->once())->method('calculateFromItems')
			->with($playlist, [])
			->willReturnSelf();

		$this->playlistMetricsCalculatorMock->expects($this->once())->method('getMetricsForPlaylistTable')
			->willReturn(['metric1' => 'value1']);

		$result = $this->itemsService->loadByPlaylistForExport($playlist, $edition);

		$this->assertArrayHasKey('playlist_metrics', $result);
		$this->assertArrayHasKey('items', $result);

		$this->assertEquals(['metric1' => 'value1'], $result['playlist_metrics']);
		$this->assertCount(0, $result['items']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchItemByIdForValidMediapoolVideo(): void
	{
		$itemId = 123;
		$itemData = [
			'item_id' => $itemId,
			'playlist_id' => 1,
			'item_type' => 'mediapool',
			'mimetype' => 'video/mp4',
			'file_resource' => 'checksum_value'
		];

		$this->itemsService->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('findFirstById')
			->with($itemId)
			->willReturn($itemData);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($itemData['playlist_id']);

		$media = ['metadata' => ['duration' => 120], 'config_data' => ''];
		$this->mediaServiceMock->expects($this->once())->method('setUID')
			->with(1);
		$this->mediaServiceMock->expects($this->once())->method('fetchMediaByChecksum')
			->with($itemData['file_resource'])
			->willReturn($media);

		$result = $this->itemsService->fetchItemById($itemId);

		$this->assertArrayHasKey('default_duration', $result);
		$this->assertEquals(120, $result['default_duration']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchItemByIdForValidMediapoolImage(): void
	{
		$itemId = 123;
		$itemData = [
			'item_id' => $itemId,
			'playlist_id' => 1,
			'item_type' => 'mediapool',
			'mimetype' => 'image/webp',
			'file_resource' => 'checksum_value',
			'config_data' => ''
		];

		$this->itemsService->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('findFirstById')
			->with($itemId)
			->willReturn($itemData);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($itemData['playlist_id']);

		$this->mediaServiceMock->expects($this->once())->method('setUID');
		$this->mediaServiceMock->expects($this->once())->method('fetchMediaByChecksum')->willReturn(['config_data' => '']);

		$this->playlistMetricsCalculatorMock->expects($this->once())->method('getDefaultDuration')
			->willReturn(17);

		$result = $this->itemsService->fetchItemById($itemId);

		$this->assertArrayHasKey('default_duration', $result);
		$this->assertEquals(17, $result['default_duration']);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchItemByIdForValidMediapoolVideoException(): void
	{
		$itemId = 123;
		$itemData = [
			'item_id' => $itemId,
			'playlist_id' => 1,
			'item_type' => 'mediapool',
			'mimetype' => 'video/mp4',
			'file_resource' => 'checksum_value'
		];

		$this->itemsService->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('findFirstById')
			->with($itemId)
			->willReturn($itemData);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($itemData['playlist_id']);

		$this->mediaServiceMock->expects($this->once())->method('setUID')
			->with(1);
		$this->mediaServiceMock->expects($this->once())->method('fetchMediaByChecksum')
			->with($itemData['file_resource'])
			->willReturn([]);

		$this->expectException(ModuleException::class);
		$this->expectExceptionMessage('Item not found');

		$this->itemsService->fetchItemById($itemId);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchItemByIdForValidPlaylistItem(): void
	{
		$itemId = 456;
		$itemData = [
			'item_id' => $itemId,
			'playlist_id' => 2,
			'item_type' => 'playlist'
		];

		$this->itemsService->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('findFirstById')
			->with($itemId)
			->willReturn($itemData);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$playlist = ['duration' => 150];
		$this->playlistsServiceMock->expects($this->exactly(2))->method('loadPureById')
			->with($itemData['playlist_id'])
			->willReturn($playlist);

		$result = $this->itemsService->fetchItemById($itemId);

		$this->assertArrayHasKey('default_duration', $result);
		$this->assertEquals(150, $result['default_duration']);
	}


	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFetchItemByIdDefaultDuration(): void
	{
		$itemId = 101;
		$itemData = [
			'item_id' => $itemId,
			'playlist_id' => 4,
			'item_type' => 'template'
		];

		$this->itemsService->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('findFirstById')
			->with($itemId)
			->willReturn($itemData);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())
			->method('loadPureById')
			->with($itemData['playlist_id']);

		$this->playlistMetricsCalculatorMock->expects($this->once())->method('getDefaultDuration')
			->willReturn(100);

		$result = $this->itemsService->fetchItemById($itemId);

		$this->assertArrayHasKey('default_duration', $result);
		$this->assertEquals(100, $result['default_duration']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadItemsByPlaylistIdForComposerWithMediapoolImage(): void
	{
		$playlistId = 1;
		$playlistData = ['playlist_id' => $playlistId];
		$itemsData = [
			[
				'item_type' => ItemType::MEDIAPOOL->value,
				'mimetype' => 'video/webm',
				'file_resource' => 'video_checksum'
			]
		];

		$this->itemsService->setUID(1);
		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($playlistId)
			->willReturn($playlistData);

		$this->mediaServiceMock->expects($this->once())->method('getPathThumbnails')
			->willReturn('/path/to/thumbnails');

		$this->itemsRepositoryMock->expects($this->once())->method('findAllByPlaylistId')
			->with($playlistId)
			->willReturn($itemsData);

		$result = $this->itemsService->loadItemsByPlaylistIdForComposer($playlistId);

		$this->assertArrayHasKey('items', $result);
		$this->assertCount(1, $result['items']);
		$this->assertEquals('/path/to/thumbnails/video_checksum.jpg', $result['items'][0]['paths']['thumbnail']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadItemsByPlaylistIdForComposerWithMediapoolVideo(): void
	{
		$playlistId = 1;
		$playlistData = ['playlist_id' => $playlistId];
		$itemsData = [
			[
				'item_type' => ItemType::MEDIAPOOL->value,
				'mimetype' => 'image/jpeg',
				'file_resource' => 'image_checksum'
			]
		];

		$this->itemsService->setUID(1);
		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($playlistId)
			->willReturn($playlistData);

		$this->mediaServiceMock->expects($this->once())->method('getPathThumbnails')
			->willReturn('/path/to/thumbnails');

		$this->itemsRepositoryMock->expects($this->once())->method('findAllByPlaylistId')
			->with($playlistId)
			->willReturn($itemsData);

		$result = $this->itemsService->loadItemsByPlaylistIdForComposer($playlistId);

		$this->assertArrayHasKey('items', $result);
		$this->assertCount(1, $result['items']);
		$this->assertEquals('/path/to/thumbnails/image_checksum.jpg', $result['items'][0]['paths']['thumbnail']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadItemsByPlaylistIdForComposerWithWidget(): void
	{
		$playlistId = 1;
		$playlistData = ['playlist_id' => $playlistId];
		$itemsData = [
			[
				'item_type' => ItemType::MEDIAPOOL->value,
				'mimetype' => 'application/widget',
				'file_resource' => 'image_checksum',
				'content_data' => ''
			]
		];

		$this->itemsService->setUID(1);
		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($playlistId)
			->willReturn($playlistData);

		$this->mediaServiceMock->expects($this->once())->method('getPathThumbnails')
			->willReturn('/path/to/thumbnails');

		$this->itemsRepositoryMock->expects($this->once())->method('findAllByPlaylistId')
			->with($playlistId)
			->willReturn($itemsData);

		$result = $this->itemsService->loadItemsByPlaylistIdForComposer($playlistId);

		$this->assertArrayHasKey('items', $result);
		$this->assertCount(1, $result['items']);
		$this->assertEquals('/path/to/thumbnails/image_checksum.svg', $result['items'][0]['paths']['thumbnail']);
	}


	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadItemsByPlaylistIdForComposerWithPlaylistType(): void
	{
		$playlistId = 1;
		$playlistData = ['playlist_id' => $playlistId];
		$itemsData = [
			[
				'item_type' => ItemType::PLAYLIST->value,
				'mimetype' => '',
				'file_resource' => ''
			]
		];

		$this->itemsService->setUID(1);
		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($playlistId)
			->willReturn($playlistData);

		$this->mediaServiceMock->expects($this->never())->method('getPathThumbnails');

		$this->itemsRepositoryMock->expects($this->once())->method('findAllByPlaylistId')
			->with($playlistId)
			->willReturn($itemsData);

		$result = $this->itemsService->loadItemsByPlaylistIdForComposer($playlistId);

		$this->assertArrayHasKey('items', $result);
		$this->assertCount(1, $result['items']);
		$this->assertEquals('public/images/icons/playlist.svg', $result['items'][0]['paths']['thumbnail']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testLoadItemsByPlaylistIdForComposerWithEmptyPlaylist(): void
	{
		$playlistId = 1;
		$playlistData = ['playlist_id' => $playlistId];

		$this->itemsService->setUID(1);
		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($playlistId)
			->willReturn($playlistData);

		$this->mediaServiceMock->expects($this->never())->method('getPathThumbnails');

		$this->itemsRepositoryMock->expects($this->once())->method('findAllByPlaylistId')
			->with($playlistId)
			->willReturn([]);

		$result = $this->itemsService->loadItemsByPlaylistIdForComposer($playlistId);

		$this->assertArrayHasKey('items', $result);
		$this->assertCount(0, $result['items']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateFieldSuccess(): void
	{
		$itemId = 1;
		$fieldName = 'name';
		$fieldValue = 'New Playlist Name';
		$playlistId = 123;

		$this->itemsService->setUID(10);

		$itemData = ['playlist_id' => $playlistId];
		$this->itemsRepositoryMock->expects($this->once())->method('findFirstById')
			->with($itemId)
			->willReturn($itemData);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(10);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($playlistId);

		$saveData = ['name' => 'New Playlist Name'];
		$this->itemsRepositoryMock->expects($this->once())->method('update')
			->with($itemId, $saveData)
			->willReturn(1);

		$result = $this->itemsService->updateField($itemId, $fieldName, $fieldValue);

		$this->assertEquals(1, $result);
	}


	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrderSuccess(): void
	{
		$playlistId = 10;
		$itemsOrder = [5 => 3, 3 => 1, 8 => 2];

		$this->itemsService->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('beginTransaction');
		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($playlistId);

		$this->itemsRepositoryMock->expects($this->exactly(3))->method('updateItemOrder')
			->willReturnMap([
				[3, 5, 1],
				[1, 3, 1],
				[2, 8, 1],
			]);
		$this->itemsRepositoryMock->expects($this->once())->method('commitTransaction');

		$this->assertTrue($this->itemsService->updateItemOrder($playlistId, $itemsOrder));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrderThrowsExceptionForInvalidPlaylist(): void
	{
		$playlistId = 10;
		$itemsOrder = [5 => 3, 3 => 1, 8 => 2];

		$this->itemsService->setUID(1);
		$this->itemsRepositoryMock->expects($this->once())->method('beginTransaction');
		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPureById')
			->with($playlistId);

		$this->itemsRepositoryMock->expects($this->once())->method('updateItemOrder')
			->with(3, 5)->willReturn(0);

		$this->itemsRepositoryMock->expects($this->once())->method('rollBackTransaction');
		$this->itemsRepositoryMock->expects($this->never())->method('commitTransaction');

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error item reorder: Item order for item_id 3 could not be updated');

		$this->assertFalse($this->itemsService->updateItemOrder($playlistId, $itemsOrder));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteItemSuccessfully(): void
	{
		$playlistId = 10;
		$itemId = 20;

		$playlistData = ['playlist_id' => $playlistId];
		$itemData = ['item_id' => $itemId, 'item_order' => 1];

		$this->itemsService->setUID(1);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPlaylistForEdit')
			->with($playlistId)
			->willReturn($playlistData);

		$this->itemsRepositoryMock->expects($this->once())
			->method('beginTransaction');

		$this->itemsRepositoryMock->expects($this->once())->method('findFirstBy')
			->with(['item_id' => $itemId])
			->willReturn($itemData);

		$this->itemsRepositoryMock->expects($this->once())->method('delete')
			->with($itemId)
			->willReturn(1);

		$this->itemsRepositoryMock->expects($this->once())->method('updatePositionsWhenDeleted')
			->with($playlistId, $itemData['item_order']);

		$metrics = ['metric1' => 'value1'];
		$this->playlistMetricsCalculatorMock->setUID(1);
		$this->playlistMetricsCalculatorMock->expects($this->once())->method('calculateFromPlaylistData')
			->with($playlistData)
			->willReturnSelf();

		$this->playlistMetricsCalculatorMock->expects($this->once())->method('getMetricsForFrontend')
			->willReturn($metrics);

		$this->itemsRepositoryMock->expects($this->once())->method('commitTransaction');

		$result = $this->itemsService->delete($playlistId, $itemId);

		$this->assertArrayHasKey('playlist_metrics', $result);
		$this->assertArrayHasKey('delete_id', $result);
		$this->assertEquals($metrics, $result['playlist_metrics']);
		$this->assertEquals(1, $result['delete_id']);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeletePlaylistNotFound(): void
	{
		$playlistId = 10;
		$itemId = 20;

		$this->itemsRepositoryMock->expects($this->once())
			->method('beginTransaction');
		$this->itemsService->setUID(1);
		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPlaylistForEdit')
			->with($playlistId)
			->willReturn([]);

		$this->itemsRepositoryMock->expects($this->never())->method('findFirstBy');

		$this->itemsRepositoryMock->expects($this->once())
			->method('rollBackTransaction');

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error delete item: Playlist is not accessible');

		$this->assertEmpty($this->itemsService->delete($playlistId, $itemId));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteItemNotFound(): void
	{
		$playlistId = 10;
		$itemId = 20;
		$playlistData = ['playlist_id' => $playlistId];

		$this->itemsService->setUID(1);

		$this->playlistsServiceMock->expects($this->once())->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())->method('loadPlaylistForEdit')
			->with($playlistId)
			->willReturn($playlistData);

		$this->itemsRepositoryMock->expects($this->once())
			->method('beginTransaction');

		$this->itemsRepositoryMock->expects($this->once())->method('findFirstBy')
			->with(['item_id' => $itemId])
			->willReturn([]);

		$this->itemsRepositoryMock->expects($this->once())
			->method('rollBackTransaction');

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error delete item: Item with idem_id: '.$itemId.' not found');

		$this->assertEmpty($this->itemsService->delete($playlistId, $itemId));
	}

	/**
	 * Test deletion fails if the item cannot be deleted.
	 *
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testDeleteItemFailure(): void
	{
		$playlistId = 10;
		$itemId = 20;

		$playlistData = ['playlist_id' => $playlistId];
		$itemData = ['item_id' => $itemId, 'item_order' => 1];

		$this->itemsService->setUID(1);

		$this->playlistsServiceMock->expects($this->once())
			->method('setUID')
			->with(1);

		$this->playlistsServiceMock->expects($this->once())
			->method('loadPlaylistForEdit')
			->with($playlistId)
			->willReturn($playlistData);

		$this->itemsRepositoryMock->expects($this->once())
			->method('beginTransaction');

		$this->itemsRepositoryMock->expects($this->once())
			->method('findFirstBy')
			->with(['item_id' => $itemId])
			->willReturn($itemData);

		$this->itemsRepositoryMock->expects($this->once())
			->method('delete')
			->with($itemId)
			->willReturn(0);

		$this->itemsRepositoryMock->expects($this->once())
			->method('rollBackTransaction');

		$this->loggerMock->expects($this->once())->method('error')
			->with('Error delete item: Item could not deleted');

		$this->assertEmpty($this->itemsService->delete($playlistId, $itemId));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemsMetricsSuccessfully(): void
	{
		$playlistId = 123;

		$this->playlistMetricsCalculatorMock->expects($this->once())
			->method('getDuration')
			->willReturn(300);

		$this->playlistMetricsCalculatorMock->expects($this->once())
			->method('getFilesize')
			->willReturn(1024);

		$saveItem = [
			'item_duration' => 300,
			'item_filesize' => 1024
		];

		$this->itemsRepositoryMock->expects($this->once())
			->method('updateWithWhere')
			->with($saveItem, ['file_resource' => $playlistId]);

		$this->itemsService->updateItemsMetrics($playlistId);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateMetricsRecursivelySuccessfully(): void
	{
		$playlistId = 2;
		$playlistsContaining = [['playlist_id' => 3]];

		$this->itemsRepositoryMock->expects($this->exactly(2))
			->method('findAllPlaylistsContainingPlaylist')
			->willReturnMap([
				[2, $playlistsContaining],
				[3, []]
			]);

		$this->playlistMetricsCalculatorMock->expects($this->once())
			->method('calculateFromPlaylistData')
			->willReturnSelf();

		$this->itemsRepositoryMock->expects($this->once())
			->method('updateWithWhere');

		$this->playlistsServiceMock->expects($this->once())
			->method('update');

		$this->itemsService->updateMetricsRecursively($playlistId);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateMetricsRecursivelyWithNoContainingPlaylists(): void
	{
		$playlistId = 1;

		$this->itemsRepositoryMock->expects($this->once())
			->method('findAllPlaylistsContainingPlaylist')
			->with($playlistId)
			->willReturn([]);

		$this->playlistMetricsCalculatorMock->expects($this->never())->method('calculateFromPlaylistData');
		$this->itemsRepositoryMock->expects($this->never())->method('updateWithWhere');
		$this->playlistsServiceMock->expects($this->never())->method('update');

		$this->itemsService->updateMetricsRecursively($playlistId);
	}
}

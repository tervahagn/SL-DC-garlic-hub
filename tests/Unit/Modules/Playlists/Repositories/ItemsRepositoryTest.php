<?php

namespace Tests\Unit\Modules\Playlists\Repositories;

use App\Framework\Core\Config\Config;
use App\Modules\Playlists\Helper\ItemType;
use App\Modules\Playlists\Repositories\ItemsRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemsRepositoryTest extends TestCase
{
	private Connection&MockObject $connectionMock;
	private QueryBuilder&MockObject $queryBuilderMock;
	private Result&MockObject $resultMock;
	private ItemsRepository $repository;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);

		$this->repository = new ItemsRepository($this->connectionMock);

		$this->connectionMock->method('createQueryBuilder')->willReturn($this->queryBuilderMock);
	}

	#[Group('units')]
	public function testConstructor(): void
	{
		$this->assertSame('playlists_items', $this->repository->getTable());
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllByPlaylistsId()
	{
		$playlistId = 67;

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('playlists_items')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');

		$this->queryBuilderMock->expects($this->once())
			->method('andWhere')
			->with('playlist_id = :playlist_id');
		$this->queryBuilderMock->expects($this->once())->method('setParameter')
			->with('playlist_id', $playlistId);

		$this->queryBuilderMock->expects($this->once())->method('addOrderBy')
			->with('item_order', 'ASC');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn(['result']);

		$this->assertEquals(['result'], $this->repository->findAllByPlaylistId($playlistId));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllByPlaylistIdWithJoinsWithCoreEdition(): void
	{
		$this->queryBuilderMock
			->expects($this->exactly(2))
			->method('leftJoin')
			->willReturnMap([
				['playlists_items',
					'mediapool_files',
					'',
					'playlists_items.file_resource = mediapool_files.checksum',
					$this->queryBuilderMock
				],
				[
					'playlists_items',
					'templates_content',
					'',
					'item_type=' . ItemType::TEMPLATE->value . ' AND  playlists_items.file_resource = templates_content.content_id',
					$this->queryBuilderMock
				]
			]);

		$this->queryBuilderMock->expects($this->once())->method('select');
		$this->queryBuilderMock->expects($this->once())->method('from')->with('playlists_items');
		$this->queryBuilderMock->expects($this->once())->method('where')->with('playlist_id = :playlistId');
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('playlistId', 1);
		$this->queryBuilderMock->expects($this->once())->method('orderBy')->with('item_order', 'ASC');
		$this->queryBuilderMock->expects($this->once())->method('groupBy')->with('playlists_items.item_id');
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')->willReturn([]);

		$this->assertSame([], $this->repository->findAllByPlaylistIdWithJoins(1, Config::PLATFORM_EDITION_CORE));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllByPlaylistIdWithJoinsWithEnterpriseEdition(): void
	{
		$this->queryBuilderMock
			->expects($this->exactly(3))
			->method('leftJoin')
			->willReturnMap([
				[
					'playlists_items',
					'mediapool_files',
					'',
					'playlists_items.file_resource = mediapool_files.checksum',
					$this->queryBuilderMock
				],
				[
					'playlists_items',
					'templates_content',
					'',
					'item_type=' . ItemType::TEMPLATE->value . ' AND  playlists_items.file_resource = templates_content.content_id',
					$this->queryBuilderMock
				],
				[
					'playlists_items',
					'channels',
					'',
					'item_type=' . ItemType::CHANNEL->value . ' AND  playlists_items.file_resource = channels.channel_id',
					$this->queryBuilderMock
				]
			]);

		$this->queryBuilderMock->expects($this->once())->method('select');
		$this->queryBuilderMock->expects($this->once())->method('from')->with('playlists_items');
		$this->queryBuilderMock->expects($this->once())->method('where')->with('playlist_id = :playlistId');
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('playlistId', 1);
		$this->queryBuilderMock->expects($this->once())->method('orderBy')->with('item_order', 'ASC');
		$this->queryBuilderMock->expects($this->once())->method('groupBy')->with('playlists_items.item_id');
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')->willReturn([]);

		$this->assertSame([], $this->repository->findAllByPlaylistIdWithJoins(1, Config::PLATFORM_EDITION_ENTERPRISE));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllByPlaylistIdWithJoinsWithUnknownEdition(): void
	{
		$this->queryBuilderMock
			->expects($this->once())
			->method('leftJoin')
			->with(
				'playlists_items',
				'mediapool_files',
				'',
				'playlists_items.file_resource = mediapool_files.checksum'
			);

		$this->queryBuilderMock->expects($this->once())->method('select');
		$this->queryBuilderMock->expects($this->once())->method('from')->with('playlists_items');
		$this->queryBuilderMock->expects($this->once())->method('where')->with('playlist_id = :playlistId');
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('playlistId', 1);
		$this->queryBuilderMock->expects($this->once())->method('orderBy')->with('item_order', 'ASC');
		$this->queryBuilderMock->expects($this->once())->method('groupBy')->with('playlists_items.item_id');
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')->willReturn([]);

		$this->assertSame([], $this->repository->findAllByPlaylistIdWithJoins(1, 'undefined_edition'));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllPlaylistItemsByPlaylistId()
	{
		$playlistId = 96;

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('playlists_items')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('andWhere')
			->willReturnMap([
				['playlist_id = :playlist_id', $this->queryBuilderMock],
				['item_type = :item_type', $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->exactly(2))->method('setParameter')
			->willReturnMap([
				['playlist_id', $playlistId, $this->queryBuilderMock],
				['item_type', ItemType::PLAYLIST->value, $this->queryBuilderMock]
			]);

		$this->queryBuilderMock->expects($this->never())->method('addOrderBy');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn(['result']);

		$this->assertEquals(['result'], $this->repository->findAllPlaylistItemsByPlaylistId($playlistId));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testSumAndCountMetricsByPlaylistIdAndOwner(): void
	{
		$playlistId = 101;
		$ownerId = 500;

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with(
				'COUNT(*) AS count_items',
				'COUNT(CASE WHEN UID = ' . $ownerId . ' THEN 1 ELSE NULL END) AS count_owner_items',
				'SUM(item_filesize) AS filesize',
				'SUM(item_duration) AS duration',
				'SUM(CASE WHEN UID = ' . $ownerId . ' THEN item_duration ELSE 0 END) AS owner_duration'
			)
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('playlists_items')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('playlist_id = :playlist_id')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('setParameter')
			->with('playlist_id', $playlistId);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn([
				'count_items' => 10,
				'count_owner_items' => 5,
				'filesize' => 1500,
				'duration' => 7200,
				'owner_duration' => 3600,
			]);

		$this->assertEquals([
			'count_items' => 10,
			'count_owner_items' => 5,
			'filesize' => 1500,
			'duration' => 7200,
			'owner_duration' => 3600,
		], $this->repository->sumAndCountMetricsByPlaylistIdAndOwner($playlistId, $ownerId));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testSumAndCountMetricsByPlaylistIdAndOwnerEmptyResult(): void
	{
		$playlistId = 101;
		$ownerId = 500;

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with(
				'COUNT(*) AS count_items',
				'COUNT(CASE WHEN UID = ' . $ownerId . ' THEN 1 ELSE NULL END) AS count_owner_items',
				'SUM(item_filesize) AS filesize',
				'SUM(item_duration) AS duration',
				'SUM(CASE WHEN UID = ' . $ownerId . ' THEN item_duration ELSE 0 END) AS owner_duration'
			)
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('playlists_items')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('where')
			->with('playlist_id = :playlist_id')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('setParameter')
			->with('playlist_id', $playlistId);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())
			->method('fetchAssociative')
			->willReturn([]);

		$this->assertEquals([], $this->repository->sumAndCountMetricsByPlaylistIdAndOwner($playlistId, $ownerId));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindAllPlaylistContainingPlaylist()
	{
		$playlistId = 96;

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('playlists_items.item_id, playlists.*')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('playlists_items')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('leftJoin')
			->with('playlists_items', 'playlists', 'playlists', 'playlists.playlist_id = playlists_items.playlist_id');

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('andWhere')
			->willReturnMap([
				['playlists_items.file_resource = :playlists_itemsfile_resource', $this->queryBuilderMock],
				['playlists_items.item_type = :playlists_itemsitem_type', $this->queryBuilderMock]
			]);
		$this->queryBuilderMock->expects($this->exactly(2))->method('setParameter')
			->willReturnMap([
				['playlists_itemsfile_resource', $playlistId, $this->queryBuilderMock],
				['playlists_itemsitem_type', ItemType::PLAYLIST->value, $this->queryBuilderMock]
			]);

		$this->queryBuilderMock->expects($this->never())->method('addOrderBy');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn(['result']);

		$this->assertEquals(['result'], $this->repository->findAllPlaylistsContainingPlaylist($playlistId));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdatePositionsWhenInsertedWithAffectedRows(): void
	{
		$playlistId = 1;
		$position = 3;

		$this->queryBuilderMock->expects($this->once())->method('update')
			->with('playlists_items')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('set')
			->with('item_order', 'item_order + 1')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('where')
			->with('playlist_id = :playlist_id');
		$this->queryBuilderMock->expects($this->once())
			->method('andWhere')
			->with('item_order >= :item_order');

		$this->queryBuilderMock
			->expects($this->exactly(2))
			->method('setParameter')
			->willReturnMap([
				['playlist_id', $playlistId, $this->queryBuilderMock],
				['item_order', $position, $this->queryBuilderMock],
			]);

		$this->queryBuilderMock->expects($this->once())->method('executeStatement')
			->willReturn(5);

		$this->assertSame(5, $this->repository->updatePositionsWhenInserted($playlistId, $position));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdatePositionsWhenDeletedWithAffectedRows(): void
	{
		$playlistId = 1;
		$position = 3;

		$this->queryBuilderMock->expects($this->once())->method('update')
			->with('playlists_items')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('set')
			->with('item_order', 'item_order - 1')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('where')
			->with('playlist_id = :playlist_id');
		$this->queryBuilderMock->expects($this->once())
			->method('andWhere')
			->with('item_order >= :item_order');

		$this->queryBuilderMock
			->expects($this->exactly(2))
			->method('setParameter')
			->willReturnMap([
				['playlist_id', $playlistId, $this->queryBuilderMock],
				['item_order', $position, $this->queryBuilderMock],
			]);

		$this->queryBuilderMock->expects($this->once())->method('executeStatement')
			->willReturn(5);

		$this->assertSame(5, $this->repository->updatePositionsWhenDeleted($playlistId, $position));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdatePositionsWhenDeletedWithNoAffectedRows(): void
	{
		$playlistId = 1;
		$position = 3;

		$this->queryBuilderMock->expects($this->once())->method('update')
			->with('playlists_items')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('set')
			->with('item_order', 'item_order - 1')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('where')
			->with('playlist_id = :playlist_id');
		$this->queryBuilderMock->expects($this->once())
			->method('andWhere')
			->with('item_order >= :item_order');

		$this->queryBuilderMock
			->expects($this->exactly(2))
			->method('setParameter')
			->willReturnMap([
				['playlist_id', $playlistId, $this->queryBuilderMock],
				['item_order', $position, $this->queryBuilderMock],
			]);

		$this->queryBuilderMock->expects($this->once())->method('executeStatement')
			->willReturn(0);

		$this->assertSame(0, $this->repository->updatePositionsWhenDeleted($playlistId, $position));
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testUpdateItemOrderSuccessfully(): void
	{
		$itemId = 10;
		$newOrder = 5;

		$this->connectionMock->expects($this->once())->method('update')
			->with('playlists_items', ['item_order' => $newOrder], ['item_id' => $itemId])
			->willReturn(1);


		$this->assertSame(1, $this->repository->updateItemOrder($itemId, $newOrder));
	}


	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindFileResourcesByPlaylistIdWithValidIds(): void
	{
		$playlistIds = [123, 456];

		$sql = "SELECT file_resource as playlist_id FROM playlists_items WHERE item_type = '" . ItemType::PLAYLIST->value . "' AND CAST(file_resource AS UNSIGNED) IN(123,456)";

		$this->connectionMock->expects($this->once())
			->method('executeQuery')
			->with($sql)
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())
			->method('fetchAllAssociative')
			->willReturn([
				['playlist_id' => '123'],
				['playlist_id' => '456']
			]);

		$this->assertEquals(
			[['playlist_id' => '123'], ['playlist_id' => '456']],
			$this->repository->findFileResourcesByPlaylistId($playlistIds)
		);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testFindFileResourcesByPlaylistIdReturnsEmptyArrayForEmptyInput(): void
	{
		$this->connectionMock->expects($this->never())
			->method('executeQuery');

		$this->assertEquals([], $this->repository->findFileResourcesByPlaylistId([]));
	}
}

<?php

namespace Tests\Unit\Modules\Player\Repositories;

use App\Modules\Player\Repositories\PlayerIndexRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PlayerIndexRepositoryTest extends TestCase
{
	private readonly Connection	 $connectionMock;
	private readonly QueryBuilder $queryBuilderMock;
	private readonly Result $resultMock;
	private PlayerIndexRepository $repository;

	/**
	 * @throws Exception
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);

		$this->repository = new PlayerIndexRepository($this->connectionMock);

		$this->connectionMock->method('createQueryBuilder')->willReturn($this->queryBuilderMock);
	}

	#[Group('units')]
	public function testConstructor(): void
	{
		$this->assertSame('player', $this->repository->getTable());
	}

	/**
	 * @throws DBALException
	 */
	#[Group('units')]
	public function testInsertPlayerSuccessfullyAddsData(): void
	{
		$saveData = [
			'player_id' => '123',
			'commands' => ['command1', 'command2'],
			'reports' => ['report1'],
			'location_data' => ['lat' => 1.23, 'long' => 4.56],
			'properties' => ['key' => 'value'],
			'remote_administration' => ['admin' => true],
			'categories' => [1, 2, 3],
			'screen_times' => ['duration' => 3600],
		];

		$processedData = [
			'player_id' => '123',
			'commands' => 'command1,command2',
			'reports' => 'report1',
			'location_data' => 'a:2:{s:3:"lat";d:1.23;s:4:"long";d:4.56;}',
			'properties' => 'a:1:{s:3:"key";s:5:"value";}',
			'remote_administration' => 'a:1:{s:5:"admin";b:1;}',
			'categories' => 'a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}',
			'screen_times' => 'a:1:{s:8:"duration";i:3600;}',
		];
		$this->connectionMock->method('insert')
			->with('player', $processedData);

		$this->connectionMock->method('lastInsertId')
			->willReturn(1);

		$result = $this->repository->insertPlayer($saveData);
		$this->assertSame(1, $result);
	}


	/**
	 * @throws DBALException
	 */
	#[Group('units')]
	public function testFindPlayerByIdReturnsEmptyArray(): void
	{
		$this->mockQueryBuilder();
		$this->mockQueryBuilderForId();
		$this->resultMock->method('fetchAssociative')
			->willReturn(false);

		$result = $this->repository->findPlayerById('123');
		$this->assertEmpty($result);
	}

	/**
	 * @throws DBALException
	 */
	#[Group('units')]
	public function testFindPlayerByIdReturnsExpandedResult(): void
	{
		$rawData = [
			'player_id' => '123',
			'commands' => 'one,two,three',
			'reports' => 'four,five,six',
			'location_data' => 'a:0:{}',
			'properties' => 'a:0:{}',
			'remote_administration' => 'a:0:{}',
			'categories' => 'a:0:{}',
			'screen_times' => 'a:0:{}',
		];

		$expandedData = [
			'player_id' => '123',
			'commands' => ['one', 'two', 'three'],
			'reports' => ['four', 'five', 'six'],
			'location_data' => [],
			'properties' => [],
			'remote_administration' => [],
			'categories' => [],
			'screen_times' => [],
		];

		$this->mockQueryBuilder();
		$this->mockQueryBuilderForId();

		$this->resultMock->method('fetchAssociative')
			->willReturn($rawData);

		$result = $this->repository->findPlayerById('123');
		$this->assertSame($expandedData, $result);
	}


	/**
	 * @throws DBALException
	 */
	#[Group('units')]
	public function testFindPlayerByUuidReturnsEmptyArray(): void
	{
		$this->mockQueryBuilder();
		$this->mockQueryBuilderForUuid();
		$this->resultMock->method('fetchAssociative')->willReturn(false);

		$result = $this->repository->findPlayerByUuid('test-uuid');
		$this->assertEmpty($result);
	}

	/**
	 * @throws DBALException
	 */
	#[Group('units')]
	public function testFindPlayerByUuidReturnsExpandedResult(): void
	{
		$rawData = [
			'player_id' => '123',
			'commands' => 'command1,command2',
			'reports' => 'report1,report2',
			'location_data' => 'a:0:{}',
			'properties' => 'a:0:{}',
			'remote_administration' => 'a:0:{}',
			'categories' => 'a:0:{}',
			'screen_times' => 'a:0:{}',
		];

		$expectedData = [
			'player_id' => '123',
			'commands' => ['command1', 'command2'],
			'reports' => ['report1', 'report2'],
			'location_data' => [],
			'properties' => [],
			'remote_administration' => [],
			'categories' => [],
			'screen_times' => [],
		];

		$this->mockQueryBuilder();
		$this->mockQueryBuilderForUuid();
		$this->resultMock->method('fetchAssociative')->willReturn($rawData);

		$result = $this->repository->findPlayerByUuid('test-uuid');
		$this->assertSame($expectedData, $result);
	}

	private function mockQueryBuilder(): void
	{
		$this->queryBuilderMock->method('select')->with(
			'player_id, status, licence_id, player.UID, uuid, player.player_name,  commands, reports, location_data, location_longitude, location_latitude, player.playlist_id, player.last_update as updated_player, properties, playlist_mode, playlist_name, multizone,playlists.last_update as last_update_playlist, categories, remote_administration, screen_times'
		);
		$this->queryBuilderMock->method('from')
			->with('player');
		$this->queryBuilderMock->method('leftJoin')
			->with('player', 'playlists', '', 'playlists.playlist_id = player.playlist_id');
	}
	private function mockQueryBuilderForId(): void
	{
		$this->queryBuilderMock->method('where')
			->with('player_id = :id');
		$this->queryBuilderMock->method('setParameters')
			->with('id', 123);
		$this->queryBuilderMock->method('executeQuery')
			->willReturn($this->resultMock);
	}

	private function mockQueryBuilderForUuid(): void
	{
		$this->queryBuilderMock
			->method('where')
			->with('uuid = :uuid');

		$this->queryBuilderMock
			->method('setParameters')
			->with(['uuid' => 'test-uuid']);

		$this->queryBuilderMock
			->method('executeQuery')
			->willReturn($this->resultMock);
	}
}

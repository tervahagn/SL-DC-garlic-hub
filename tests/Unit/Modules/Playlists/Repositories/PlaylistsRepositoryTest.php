<?php

namespace Tests\Unit\Modules\Playlists\Repositories;

use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class PlaylistsRepositoryTest extends TestCase
{
	private readonly Connection	 $connectionMock;
	private readonly QueryBuilder $queryBuilderMock;
	private readonly Result $resultMock;
	private readonly PlaylistsRepository $repository;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);

		$this->repository = new PlaylistsRepository($this->connectionMock);

		$this->connectionMock->method('createQueryBuilder')->willReturn($this->queryBuilderMock);
	}
	#[Group('units')]
	public function testPlaylistMode(): void
	{
		$fields = [
			'playlist_name' => ['value' => 'm']
		];

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('COUNT(1)')->willReturnSelf();

		$this->queryBuilderMock->expects($this->exactly(3))->method('andWhere')
			->willReturnMap([
					['user_main.username LIKE :user_mainusername', $this->queryBuilderMock],
					['user_main.company_id = :user_maincompany_id', $this->queryBuilderMock],
					['table.randomfield LIKE :tablerandomfield', $this->queryBuilderMock]
				]
			);
		$this->queryBuilderMock->expects($this->exactly(3))->method('setParameter')
			->willReturnMap([
					['user_mainusername', '%john%', $this->queryBuilderMock],
					['user_maincompany_id', 123, $this->queryBuilderMock],
					['tablerandomfield', '%randomvalue%', $this->queryBuilderMock]
				]
			);


		$expectedCount = 42;
		$this->resultMock->expects($this->once())->method('fetchOne')->willReturn($expectedCount);

		$result = $this->repository->countAllFiltered($fields);
		$this->assertSame($expectedCount, $result);

	}

	private function setStandardMocks(): void
	{
		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('table')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('leftJoin')
			->with('table', 'user_main', 'user_main', 'test.UID = user_main.UID')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);
	}


	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testDeleteReturnsExpectedResult(): void
	{
		$id = 1;
		$expectedResult = 1;
		$sqliteplatform = new \Doctrine\DBAL\Platforms\SQLitePlatform();
		$this->connectionMock->expects($this->once())
			->method('getDatabasePlatform')
			->willReturn($sqliteplatform);

		$this->connectionMock->expects($this->once())->method('executeQuery')
			->with('PRAGMA foreign_keys = ON');

		$this->connectionMock->expects($this->once())->method('delete')
			->with('playlists', ['playlist_id' => $id])
			->willReturn($expectedResult);

		$result = $this->repository->delete($id);

		$this->assertSame($expectedResult, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindFirstWithUserNameReturnsExpectedData(): void
	{
		$playlistId = 1;
		$expectedData = [
			'playlist_id' => $playlistId,
			'title' => 'Sample Playlist',
			'username' => 'test_user',
			'company_id' => 123
		];

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('playlists.*, user_main.username, user_main.company_id')
			->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('from')->with('playlists');
		$this->queryBuilderMock->expects($this->once())->method('andWhere')->with('playlist_id = :playlist_id');
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('playlist_id', $playlistId);
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')->willReturn([$expectedData]);

		$result = $this->repository->findFirstWithUserName($playlistId);

		$this->assertSame($expectedData, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testUpdateExportUpdatesDataSuccessfully(): void
	{
		$playlistId     = 1;
		$saveData       = ['title' => 'Updated Playlist', 'description' => 'Updated Description'];
		$expectedResult = 1;

		$this->queryBuilderMock->expects($this->once())->method('update')->with('playlists')->willReturnSelf();

		$this->queryBuilderMock->expects($this->exactly(3))->method('set')
			->willReturnMap([
				['title', 'Updated Playlist', $this->queryBuilderMock],
				['description', 'Updated Description', $this->queryBuilderMock],
				['export_time', 'CURRENT_TIMESTAMP', $this->queryBuilderMock]
			]);

		$this->queryBuilderMock->expects($this->once())->method('where')->with('playlist_id = :playlist_id')->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('setParameter')
			->with('playlist_id', $playlistId, \Doctrine\DBAL\ParameterType::INTEGER)->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('executeStatement')->willReturn($expectedResult);

		$result = $this->repository->updateExport($playlistId, $saveData);

		$this->assertSame($expectedResult, $result);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testUpdateExportThrowsException(): void
	{
		$playlistId = 1;
		$saveData = ['title' => 'Updated Playlist'];

		$this->queryBuilderMock->expects($this->once())->method('update')->with('playlists')->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('set')
			->with('title', 'Updated Playlist')->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('set')->with('export_time', 'CURRENT_TIMESTAMP')->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('where')->with('playlist_id = :playlist_id')->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('setParameter')
			->with('playlist_id', $playlistId, \Doctrine\DBAL\ParameterType::INTEGER)->willReturnSelf();

		$this->queryBuilderMock->expects($this->once())->method('executeStatement')
			->willThrowException(new Exception('Database error'));

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Database error');

		$this->repository->updateExport($playlistId, $saveData);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testCountAllFilteredReturnsCorrectCount(): void
	{
		$fields = [
			'playlist_mode' => ['value' => 'multizone'],
			'playlist_name' => ['value' => 'name']
		];

		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('COUNT(1)')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('from')->with('playlists');

		$this->queryBuilderMock->expects($this->once())->method('leftJoin')
			->with('playlists', 'user_main', 'user_main', 'user_main.UID=playlists.UID')->willReturnSelf();


		$this->queryBuilderMock->expects($this->exactly(2))->method('andWhere')
			->willReturnMap([
					['playlist_mode = :playlist_mode', $this->queryBuilderMock],
					['playlists.playlist_name LIKE :playlistsplaylist_name', $this->queryBuilderMock]
				]
			);
		$this->queryBuilderMock->expects($this->exactly(2))->method('setParameter')
			->willReturnMap([
					['playlist_mode', 'multizone', $this->queryBuilderMock],
					['playlistsplaylist_name', '%name%', $this->queryBuilderMock]
				]
			);

		$expectedCount = 67;
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);
		$this->resultMock->expects($this->once())->method('fetchOne')->willReturn($expectedCount);

		$result = $this->repository->countAllFiltered($fields);
		$this->assertSame($expectedCount, $result);
	}
}

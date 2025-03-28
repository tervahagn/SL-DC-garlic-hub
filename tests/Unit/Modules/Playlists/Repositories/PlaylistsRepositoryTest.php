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
			->with('playlists', 'user_main', '', 'user_main.UID=playlists.UID')->willReturnSelf();


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

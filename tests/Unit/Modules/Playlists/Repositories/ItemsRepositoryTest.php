<?php

namespace Tests\Unit\Modules\Playlists\Repositories;

use App\Modules\Playlists\Repositories\ItemsRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ItemsRepositoryTest extends TestCase
{
	private readonly Connection	 $connectionMock;
	private readonly QueryBuilder $queryBuilderMock;
	private readonly Result $resultMock;
	private readonly ItemsRepository $repository;

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


}

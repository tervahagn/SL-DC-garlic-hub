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

namespace Tests\Unit\Modules\Mediapool\Repositories;

use App\Framework\Exceptions\DatabaseException;
use App\Modules\Mediapool\Repositories\NodesRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NodesRepositoryTest extends TestCase
{
	private Connection&MockObject $connectionMock;
	private QueryBuilder&MockObject $queryBuilderMock;
	private Result&MockObject $resultMock;
	private NodesRepository $nodesRepository;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->connectionMock = $this->createMock(Connection::class);
		$this->queryBuilderMock  = $this->createMock(QueryBuilder::class);
		$this->resultMock = $this->createMock(Result::class);

		$this->nodesRepository = new NodesRepository($this->connectionMock);
	}

	/**
	 * @throws Exception
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	#[Group('units')]
	public function testGetNodeReturnsCorrectData(): void
	{
		$node_id = 1;
		$select = 'mediapool_nodes.UID, username, company_id, node_id, visibility, root_id, is_user_folder, parent_id, level, lft, rgt, last_updated, create_date, name, media_location, ROUND((rgt - lft - 1) / 2) AS children';

		$this->connectionMock->expects($this->once())
			->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock );

		$this->queryBuilderMock->expects($this->once())
			->method('select')
			->with($select)
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())
			->method('from')
			->with('mediapool_nodes');

		$this->queryBuilderMock->expects($this->once())
			->method('andWhere')
			->willReturnCallback(function ($condition) {
				$expectedConditions = ['node_id = :node_id'];
				$this->assertContains($condition, $expectedConditions);
				return $this->queryBuilderMock;
			});
		$this->queryBuilderMock->expects($this->exactly(1))
			->method('setParameter')
			->willReturnCallback(function ($name, $value) {
				$expectedNames = ['node_id'];
				$expectedValues = [1];
				$this->assertContains($name, $expectedNames);
				$this->assertContains($value, $expectedValues);
				return $this->queryBuilderMock;
			});


		$this->queryBuilderMock->method('executeQuery')->willReturn($this->resultMock);
		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([]);

		$this->assertEmpty($this->nodesRepository->getNode($node_id));
	}

	/**
	 * @throws Exception|DatabaseException
	 */
	#[Group('units')]
	public function testFindNodeOwner(): void
	{
		$nodeId = 2;
		$expectedResult = ['UID' => 1, 'node_id' => 2, 'name' => 'Child Node 1', 'company_id' => 1];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		// ... (Mock query builder methods)

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn($expectedResult);

		$actualResult = $this->nodesRepository->findNodeOwner($nodeId);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindNodeOwnerNotFound(): void
	{
		$nodeId = 999; // Non-existent node ID

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		// ... (Mock query builder methods)

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAssociative')
			->willReturn(false); // Simulate no result

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Node not found');

		$this->nodesRepository->findNodeOwner($nodeId);
	}
}

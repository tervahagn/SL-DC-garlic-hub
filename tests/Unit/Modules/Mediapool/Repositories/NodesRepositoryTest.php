<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace Tests\Unit\Modules\Mediapool\Repositories;

use App\Framework\Database\BaseRepositories\NestedSetHelper;
use App\Modules\Mediapool\Repositories\NodesRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NodesRepositoryTest extends TestCase
{
	private readonly Connection&MockObject $connectionMock;
	private readonly QueryBuilder&MockObject $queryBuilderMock;
	private readonly NestedSetHelper&MockObject $helperMock;
	private readonly LoggerInterface&MockObject $loggerMock;
	private readonly NodesRepository $nodesRepository;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock = $this->createMock(Connection::class);
		$this->queryBuilderMock  = $this->createMock(QueryBuilder::class);
		$this->helperMock       = $this->createMock(NestedSetHelper::class);
		$this->loggerMock       = $this->createMock(LoggerInterface::class);

		$this->nodesRepository = new NodesRepository($this->connectionMock, $this->helperMock, $this->loggerMock);
	}

	/**
	 * @throws Exception
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	#[Group('units')]
	public function testGetNodeReturnsCorrectData()
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

		$resultMock = $this->createMock(Result::class);

		$this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
		$resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([]);

		$this->assertEmpty($this->nodesRepository->getNode($node_id));

	}
}

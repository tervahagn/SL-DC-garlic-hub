<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace Tests\Unit\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\Sql;
use App\Framework\Database\BaseRepositories\SqlBase;
use App\Framework\Database\BaseRepositories\Traits\CrudTraits;
use App\Framework\Database\BaseRepositories\Traits\FindOperationsTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ConcreteTrait extends SqlBase
{
	use CrudTraits, FindOperationsTrait;
}
class FindOperationsTraitTest extends TestCase
{
	private Connection $connectionMock;
	private QueryBuilder $queryBuilderMock;
	private ConcreteTrait $repository;
	private Result $resultMock;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock = $this->createMock(Result::class);
		$this->repository = new ConcreteTrait($this->connectionMock, 'test_table', 'test_id');
	}

	#[Group('units')]
	public function testFindFirstById()
	{
		$id = '65';
		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('where')->with('test_id = :id')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('id', $id)
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([[1], [2], [3]]);

		$this->assertEquals([1], $this->repository->findFirstById($id));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testGetFirstByWithEmpty()
	{
		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');
		$this->queryBuilderMock->expects($this->never())->method('andWhere');
		$this->queryBuilderMock->expects($this->never())->method('setParameter');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');
		$this->queryBuilderMock->expects($this->never())->method('orderBy');
		$this->queryBuilderMock->expects($this->never())->method('setFirstResult');
		$this->queryBuilderMock->expects($this->never())->method('setMaxResults');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([]);

		$this->assertEquals([], $this->repository->findFirstBy([]));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindById()
	{
		$id = '888';
		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('where')->with('test_id = :id')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('id', $id)
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn([[1], [2]]);

		$this->assertEquals([[1], [2]], $this->repository->findById($id));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCountAll()
	{
		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('COUNT(1)')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchOne')
			->willReturn(32);

		$this->assertEquals(32, $this->repository->countAll());
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCountAllBy()
	{
		$conditions = ['test_id' => 888];
		$joins = [
			'test_table2' => 'test_table2.test_id = test_table.test_id',
			'test_table3' => 'test_table3.test_id = test_table.test_id'
		];
		$groupBy = 'test_table.test_id';

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('COUNT(1)')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->exactly(count($joins)))
			->method('leftJoin')
			->willReturnCallback(function ($mainTable, $joinTable, $alias, $onCondition) use ($joins)
			{
				foreach ($joins as $expectedTable => $expectedCondition)
				{
					if (
						$mainTable === 'test_table' &&
						$joinTable === $expectedTable &&
						$alias === $joinTable &&
						$onCondition === $expectedCondition)
					{
						return $this->queryBuilderMock;
					} // For method chaining

				}
				throw new InvalidArgumentException("Unexpected join parameters: $mainTable, $joinTable, $alias, $onCondition");
			});

		$this->queryBuilderMock->expects($this->once())->method('andWhere')->with('test_id = :test_id')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('setParameter')->with('test_id', 888)
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('groupBy')->with($groupBy);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchOne')
			->willReturn(16);

		$this->assertEquals(16, $this->repository->countAllBy($conditions, $joins, $groupBy));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCountAllByRestrict()
	{
		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('COUNT(1)')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');
		$this->queryBuilderMock->expects($this->never())->method('andWhere');
		$this->queryBuilderMock->expects($this->never())->method('setParameter');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchOne')
			->willReturn(64);

		$this->assertEquals(64, $this->repository->countAllBy());
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindAllBy()
	{
		$conditions = [
			'test_id' => 4711,
			'test_username' => 'Horst Erwin Günther Günzel'
		];
		$joins = [
			'test_user_table1' => 'test_user_table2.test_id = test_table.test_id',
			'test_user_group_table3' => 'test_user_group_table3.test_id = test_table.test_id'
		];
		$groupBy = 'test_table.test_id';
		$orderBy = ['sort' => 'test_table.username', 'order' => 'ASC'];
		$limitStart = 10;
		$limitShow = 20;

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->exactly(count($joins)))
			->method('leftJoin')
			->willReturnCallback(function ($mainTable, $joinTable, $alias, $onCondition) use ($joins)
			{
				foreach ($joins as $expectedTable => $expectedCondition)
				{
					if (
						$mainTable === 'test_table' && $joinTable === $expectedTable &&
						$alias === $joinTable && $onCondition === $expectedCondition)
					{
						return $this->queryBuilderMock;
					} // For method chaining

				}
				throw new InvalidArgumentException("Unexpected join parameters: $mainTable, $joinTable, $alias, $onCondition");
			});
		$this->queryBuilderMock->expects($this->exactly(count($conditions)))
			->method('andWhere')
			->willReturnCallback(function ($condition)
			{
				$expectedConditions = ['test_id = :test_id', 'test_username = :test_username'];
				$this->assertContains($condition, $expectedConditions);
				return $this->queryBuilderMock;
			});
		$this->queryBuilderMock->expects($this->exactly(count($conditions)))->method('setParameter')
			->willReturnCallback(function ($name, $value)
			{
				$expectedNames = ['test_id', 'test_username'];
				$expectedValues = [4711, 'Horst Erwin Günther Günzel'];
				$this->assertContains($name, $expectedNames);
				$this->assertContains($value, $expectedValues);
				return $this->queryBuilderMock;
			});
		$this->queryBuilderMock->expects($this->once())->method('groupBy')->with($groupBy);
		$this->queryBuilderMock->expects($this->once())->method('addOrderBy')->with($orderBy['sort'], $orderBy['order']);
		$this->queryBuilderMock->expects($this->once())->method('setFirstResult')->with($limitStart)
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('setMaxResults')->with($limitShow);
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn(['hurz', 'wurz']);

		$limit = ['first' => $limitStart, 'max' => $limitShow];
		$this->assertEquals(['hurz', 'wurz'], $this->repository->findAllBy($conditions, $joins, $limit, $groupBy, [$orderBy]));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindAllByWithFields()
	{
		$fields = ['test_id', 'test_username', 'test_email'];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('test_id, test_username, test_email')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');
		$this->queryBuilderMock->expects($this->never())->method('andWhere');
		$this->queryBuilderMock->expects($this->never())->method('setParameter');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');
		$this->queryBuilderMock->expects($this->never())->method('addOrderBy');
		$this->queryBuilderMock->expects($this->never())->method('setFirstResult');
		$this->queryBuilderMock->expects($this->never())->method('setMaxResults');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn(['heidewitzka', 'der Kapitän']);

		$this->assertEquals(['heidewitzka', 'der Kapitän'], $this->repository->findAllByWithFields($fields));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindAllByWithLimits()
	{
		$limitStart = 1;
		$limitShow = 20;
		$orderBy = ['sort' => 'test_table.username', 'order' => 'ASC'];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with('*')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');
		$this->queryBuilderMock->expects($this->never())->method('andWhere');
		$this->queryBuilderMock->expects($this->never())->method('setParameter');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');
		$this->queryBuilderMock->expects($this->once())->method('addOrderBy')->with($orderBy['sort'], $orderBy['order']);
		$this->queryBuilderMock->expects($this->once())->method('setFirstResult')->with($limitStart)
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('setMaxResults')->with($limitShow);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn(['Highway to hell', 'Stairway to heaven']);

		$limit = ['first' => $limitStart, 'max' => $limitShow];
		$this->assertEquals(['Highway to hell', 'Stairway to heaven'], $this->repository->findAllByWithLimits($limit, [$orderBy]));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindOneValueBy()
	{
		$field = 'sinatra_songs';

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('select')->with($field)
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');
		$this->queryBuilderMock->expects($this->never())->method('andWhere');
		$this->queryBuilderMock->expects($this->never())->method('setParameter');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');
		$this->queryBuilderMock->expects($this->never())->method('orderBy');
		$this->queryBuilderMock->expects($this->never())->method('setFirstResult');
		$this->queryBuilderMock->expects($this->never())->method('setMaxResults');

		$this->queryBuilderMock->expects($this->once())->method('fetchOne')
			->willReturn('Everytime we say goodbye, I die a little');

		$this->assertEquals('Everytime we say goodbye, I die a little', $this->repository->findOneValueBy($field));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFindOneValueByRetrunsNull()
	{

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('select')->with('none')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('from')->with('test_table')
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->never())->method('leftJoin');
		$this->queryBuilderMock->expects($this->never())->method('andWhere');
		$this->queryBuilderMock->expects($this->never())->method('setParameter');
		$this->queryBuilderMock->expects($this->never())->method('groupBy');
		$this->queryBuilderMock->expects($this->never())->method('orderBy');
		$this->queryBuilderMock->expects($this->never())->method('setFirstResult');
		$this->queryBuilderMock->expects($this->never())->method('setMaxResults');

		$this->queryBuilderMock->expects($this->once())->method('fetchOne')
			->willReturn(null);

		$this->assertEquals('', $this->repository->findOneValueBy('none'));
	}


	#[Group('units')]
	public function testGetFirstDataset()
	{
		// if empty
		$this->assertEmpty($this->repository->getFirstDataSet([]));

		//with data
		$data = ['first', 'second', 'third'];
		$this->assertEquals('first', $this->repository->getFirstDataSet($data));

	}
}
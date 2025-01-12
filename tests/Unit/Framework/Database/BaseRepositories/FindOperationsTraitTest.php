<?php

namespace Tests\Unit\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\Sql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SqlConcreteTrait extends Sql{}
class FindOperationsTraitTest extends TestCase
{
	private Connection $connectionMock;
	private QueryBuilder $queryBuilderMock;
	private SqlConcreteTrait $repository;
	private Result $resultMock;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock = $this->createMock(Result::class);
		$this->repository = new SqlConcreteTrait($this->connectionMock, 'test_table', 'test_id');
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
			->willReturn([array(1, 2)]);

		$this->assertEquals([array(1, 2)], $this->repository->findById($id));
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
						$alias === $expectedTable &&
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
		$orderBy = 'test_table.username ASC';
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
						$alias === $expectedTable && $onCondition === $expectedCondition)
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
		$this->queryBuilderMock->expects($this->once())->method('orderBy')->with($orderBy);
		$this->queryBuilderMock->expects($this->once())->method('setFirstResult')->with($limitStart)
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('setMaxResults')->with($limitShow);
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn(['hurz', 'wurz']);

		$this->assertEquals(['hurz', 'wurz'], $this->repository->findAllBy($conditions, $joins, $limitStart,
			$limitShow, $groupBy, $orderBy));
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
		$this->queryBuilderMock->expects($this->never())->method('orderBy');
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
		$orderBy = 'test_table.username ASC';

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
		$this->queryBuilderMock->expects($this->once())->method('orderBy')->with($orderBy);
		$this->queryBuilderMock->expects($this->once())->method('setFirstResult')->with($limitStart)
			->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('setMaxResults')->with($limitShow);

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')
			->willReturn($this->resultMock);

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')
			->willReturn(['Highway to hell', 'Stairway to heaven']);

		$this->assertEquals(['Highway to hell', 'Stairway to heaven'], $this->repository->findAllByWithLimits($limitStart,
			$limitShow, $orderBy));
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
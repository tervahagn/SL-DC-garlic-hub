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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SqlConcrete extends Sql
{
	/**
	 * @throws Exception
	 */
	public function testFetchAssociative(QueryBuilder $queryBuilder): array
	{
		return $this->fetchAssociative($queryBuilder);
	}

	public function testSecureExplode(string $data): array
	{
		return $this->secureExplode($data);
	}

	public function testSecureUnserialize(string $data): array
	{
		return $this->secureUnserialize($data);
	}

}
class SqlTest extends TestCase
{
	private Connection	 $connectionMock;
	private QueryBuilder $queryBuilderMock;
	private SqlConcrete $repository;

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->repository       = new SqlConcrete($this->connectionMock, 'table', 'id');
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testInsert()
	{
		$fields = [
			'field1' => 'field 1 value',
			'field2' => 'field 2 value'
		];
		$this->connectionMock->expects($this->once())->method('insert')
			->with('table', $fields);

		$this->connectionMock->expects($this->once())->method('lastInsertId')
			->willReturn(1);

		$this->assertEquals(1, $this->repository->insert($fields));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testUpdate()
	{
		$fields = [
			'field1' => 'field 1 value',
			'field2' => 'field 2 value'
		];
		$this->connectionMock->expects($this->once())->method('update')
			->with('table', $fields, ['id' => 34])
			->willReturn(2);

		$this->assertEquals(2, $this->repository->update(34, $fields));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testUpdateWithWhere()
	{
		$fields = [
			'field1' => 'field 1 value',
			'field2' => 'field 2 value'
		];
		$conditions = [
			'condition1' => 'condition 1 value',
			'condition2' => 'condition 2 value'
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('update')->with('table');

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('set')
			->willReturnCallback(function ($field, $param) {
				$expectedFields = ['field1', 'field2'];
				$expectedParams = [':set_field1', ':set_field2'];
				$this->assertContains($field, $expectedFields);
				$this->assertContains($param, $expectedParams);
				return $this->queryBuilderMock;
			});

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('andWhere')
			->willReturnCallback(function ($condition) {
				$expectedConditions = ['condition1 = :condition1', 'condition2 = :condition2'];
				$this->assertContains($condition, $expectedConditions);
				return $this->queryBuilderMock;
			});

		$this->queryBuilderMock->expects($this->exactly(4))
			->method('setParameter')
			->willReturnCallback(function ($name, $value) {
				$expectedNames = ['set_field1', 'set_field2', 'condition1', 'condition2'];
				$expectedValues = ['field 1 value', 'field 2 value', 'condition 1 value', 'condition 2 value'];
				$this->assertContains($name, $expectedNames);
				$this->assertContains($value, $expectedValues);
				return $this->queryBuilderMock;
			});


		$this->queryBuilderMock->expects($this->once())->method('executeStatement')
			->willReturn(1);

		$this->assertEquals(1, $this->repository->updateWithWhere($fields, $conditions));

	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testDelete()
	{
		$this->connectionMock->expects($this->once())->method('delete')
			->with('table', ['id' => 36])
			->willReturn(17);

		$this->assertEquals(17, $this->repository->delete(36));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testDeleteByField()
	{
		$this->connectionMock->expects($this->once())->method('delete')
			->with('table', ['field' => 'value'])
			->willReturn(94);

		$this->assertEquals(94, $this->repository->deleteByField('field', 'value'));
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testDeleteBy()
	{
		$conditions = [
			'condition1' => 'condition1_value',
			'condition2' => 'condition2_value'
		];

		$this->connectionMock->expects($this->once())->method('createQueryBuilder')
			->willReturn($this->queryBuilderMock);

		$this->queryBuilderMock->expects($this->once())->method('delete')->with('table');

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('andWhere')
			->willReturnCallback(function ($condition) {
				$expectedConditions = ['condition1 = :condition1', 'condition2 = :condition2'];
				$this->assertContains($condition, $expectedConditions);
				return $this->queryBuilderMock;
			});

		$this->queryBuilderMock->expects($this->exactly(2))
			->method('setParameter')
			->willReturnCallback(function ($name, $value) {
				$expectedNames = ['condition1', 'condition2'];
				$expectedValues = ['condition1_value', 'condition2_value'];
				$this->assertContains($name, $expectedNames);
				$this->assertContains($value, $expectedValues);
				return $this->queryBuilderMock;
			});


		$this->queryBuilderMock->expects($this->once())->method('executeStatement')
			->willReturn(365);

		$this->assertEquals(365, $this->repository->deleteBy($conditions));
	}

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFetchAssociativeFails()
	{
		$resultMock = $this->createMock(Result::class);
		$this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
		$resultMock->method('fetchAssociative')->willReturn(false);

		$this->assertEmpty($this->repository->testFetchAssociative($this->queryBuilderMock));
	}

	/**
	 * @throws \PHPUnit\Framework\MockObject\Exception
	 * @throws Exception
	 */
	#[Group('units')]
	public function testFetchAssociativeSucceed()
	{
		$resultMock = $this->createMock(Result::class);
		$this->queryBuilderMock->method('executeQuery')->willReturn($resultMock);
		$expected = ['some' => 'result'];
		$resultMock->method('fetchAssociative')->willReturn($expected);

		$this->assertSame($expected, $this->repository->testFetchAssociative($this->queryBuilderMock));
	}

	#[Group('units')]
	public function testSecureExplodeEmpty()
	{
		$this->assertEmpty($this->repository->testSecureExplode(''));
	}

	#[Group('units')]
	public function testSecureExplode()
	{
		$expected = ['some'];
		$this->assertSame($expected, $this->repository->testSecureExplode('some'));

		$expected = ['some', 'result'];
		$this->assertSame($expected, $this->repository->testSecureExplode('some,result'));
	}

	#[Group('units')]
	public function testSecureUnserializeEmpty()
	{
		$this->assertEmpty($this->repository->testSecureUnserialize(''));
	}

	#[Group('units')]
	public function testSecureUnserializeError()
	{
		$this->assertEmpty($this->repository->testSecureUnserialize('mbmb'));
	}

	#[Group('units')]
	public function testSecureUnserialize()
	{
		$expected = ['some', 'array', 'result'];
		$result = $this->repository->testSecureUnserialize(serialize($expected));
		$this->assertSame($expected, $result);
	}

}

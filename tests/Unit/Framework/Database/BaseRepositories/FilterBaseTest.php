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


namespace Tests\Unit\Framework\Database\BaseRepositories;

use App\Framework\Database\BaseRepositories\FilterBase;
use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ConcreteFilterBase extends FilterBase
{
	protected function prepareJoin(): array
	{
		return ['user_main' => 'test.UID = user_main.UID'];
	}

	protected function prepareSelectFiltered(): array
	{
		return ['selected_fitered'];
	}

	protected function prepareSelectFilteredForUser(): array
	{
		return ['selected_fitered_user'];
	}

	protected function prepareWhereForFiltering(array $filterFields): array
	{
		$where = [];
		foreach ($filterFields as $key => $parameter)
		{
			$clause = $this->determineWhereForFiltering($key, $parameter);
			if (!empty($clause))
				$where = array_merge($where, $clause);
		}
		return $where;
	}
}
class FilterBaseTest extends TestCase
{
	private readonly Connection	 $connectionMock;
	private readonly QueryBuilder $queryBuilderMock;
	private readonly Result $resultMock;
	private readonly ConcreteFilterBase $FilterBase;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->connectionMock   = $this->createMock(Connection::class);
		$this->queryBuilderMock = $this->createMock(QueryBuilder::class);
		$this->resultMock       = $this->createMock(Result::class);

		$this->FilterBase = new ConcreteFilterBase($this->connectionMock, 'table', 'id');
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testCountAllFilteredReturnsCorrectCount(): void
	{
		$fields = [
			'username' => ['value' => 'john'],
			'company_id' => ['value' => 123],
			'randomfield' => ['value' => 'randomvalue']
		];

		$this->setStandardMocksForCounts();

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

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);

		$expectedCount = 42;
		$this->resultMock->expects($this->once())->method('fetchOne')->willReturn($expectedCount);

		$result = $this->FilterBase->countAllFiltered($fields);
		$this->assertSame($expectedCount, $result);
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[Group('units')]
	public function testCountAllFilteredReturnsWithNoWhere(): void
	{
		$fields = [
			'username' => ['value' => ''],
			BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE=> ['value' => 12],
			'randomfield' => ['value' => '']
		];

		$this->setStandardMocksForCounts();

		$this->queryBuilderMock->expects($this->never())->method('andWhere');
		$this->queryBuilderMock->expects($this->never())->method('setParameter');

		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);

		$expectedCount = 333;
		$this->resultMock->expects($this->once())->method('fetchOne')->willReturn($expectedCount);
		$this->assertSame($expectedCount,  $this->FilterBase->countAllFiltered($fields));
	}

	#[Group('units')]
	public function testCountAllFilteredReturnsAllEmpty(): void
	{
		$fields = [];

		$this->setStandardMocksForCounts();

		$expectedCount = 888;
		$this->resultMock->expects($this->once())->method('fetchOne')->willReturn($expectedCount);
		$this->assertSame($expectedCount,  $this->FilterBase->countAllFiltered($fields));
	}

	#[Group('units')]
	public function testFindAllFiltered(): void
	{
		$fields = [
			BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE => ['value' => 0],
			BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE => ['value' => 10],
			BaseFilterParametersInterface::PARAMETER_SORT_COLUMN => ['value' => 'column_name'],
			BaseFilterParametersInterface::PARAMETER_SORT_ORDER  => ['value' => 'DESC']
		];

		$this->setStandardMocksForFinds();

		$this->queryBuilderMock->expects($this->once())->method('addOrderBy')
			->with('table.column_name', 'DESC');

		$expectedResults = [
			['id' => 1, 'name' => 'John Doe'],
			['id' => 2, 'name' => 'Jane Doe']
		];

		$this->resultMock->expects($this->once())->method('fetchAllAssociative')->willReturn($expectedResults);

		$result = $this->FilterBase->findAllFiltered($fields);
		$this->assertSame($expectedResults, $result);
	}


	private function setStandardMocksForCounts(): void
	{
		$this->connectionMock->method('createQueryBuilder')->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('COUNT(1)')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('table')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('leftJoin')
			->with('table', 'user_main', '', 'test.UID = user_main.UID')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);
	}

	private function setStandardMocksForFinds(): void
	{
		$this->connectionMock->method('createQueryBuilder')->willReturn($this->queryBuilderMock);
		$this->queryBuilderMock->expects($this->once())->method('select')
			->with('selected_fitered_user')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('from')
			->with('table')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('leftJoin')
			->with('table', 'user_main', '', 'test.UID = user_main.UID')->willReturnSelf();
		$this->queryBuilderMock->expects($this->once())->method('executeQuery')->willReturn($this->resultMock);
	}


}

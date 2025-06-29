<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Framework\Database\BaseRepositories\Traits;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;

trait FindOperationsTrait
{

	/**
	 * @return array<string,mixed>|array<empty,empty>
	 * @throws Exception
	 */
	public function findFirstById(int|string $id): array
	{
		return $this->getFirstDataSet($this->findById($id));
	}

	/**
	 * Finds a record by ID.
	 *
	 * @param int|string $id Record ID
	 * @return list<array<string,mixed>>
	 * @throws Exception
	 */
	public function findById(int|string $id): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('*')
			->from($this->table)
			->where($this->idField . ' = :id')
			->setParameter('id', $id);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * @return array<string,mixed>|array<empty,empty>
	 * @throws Exception
	 */
	public function findFirstBy(array $conditions = []): array
	{
		return $this->getFirstDataSet($this->findAllByWithFields(['*'], $conditions));
	}


	/**
	 * Counts all records in the table.
	 *
	 * @throws Exception
	 */
	public function countAll(): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('COUNT(1)')->from($this->table);

		return (int) $queryBuilder->executeQuery()->fetchOne();
	}

	/**
	 * Counts records in the table with a custom WHERE clause.
	 * @param array<string,mixed> $conditions
	 *
	 * @throws Exception
	 */
	public function countAllBy(array $conditions = [], array $joins = [], string $groupBy = ''): int
	{
		$queryBuilder = $this->buildQuery('COUNT(1)', $conditions, $joins, $groupBy);

		return (int) $queryBuilder->executeQuery()->fetchOne();
	}

	/**
	 * Finds records with a custom WHERE clause.
	 *
	 * @return list<array<string,mixed>>
	 * @throws Exception
	 */
	public function findAllBy(array $conditions = [], array $joins = [], array $limit = [], string $groupBy = '', array $orderBy = []): array
	{
		return $this->findAllByWithFields(array('*'), $conditions, $joins, $limit, $groupBy, $orderBy);
	}

	/**
	 * Finds records with specific fields and a custom WHERE clause.
 	 * @param string[] $fields
	 * @param array<string,mixed> $conditions
	 * @param array<string,string> $joins
	 * @param array<string,int> $limit
	 * @param string $groupBy
	 * @param list<array<string,mixed>> $orderBy
	 * @return list<array<string,mixed>>
	 * @throws Exception
	 */
	public function findAllByWithFields(array $fields, array $conditions = [], array $joins = [], array $limit = [], string $groupBy = '', array $orderBy = []): array
	{
		$fields       = implode(', ', $fields);
		$queryBuilder = $this->buildQuery($fields, $conditions, $joins,	$groupBy, $orderBy);

		if (!empty($limit))
			$queryBuilder->setFirstResult($limit['first'])->setMaxResults($limit['max']);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * Finds records with limits and sorting.
	 *
	 * @throws Exception
	 */
	public function findAllByWithLimits(array $limit = [], array $orderBy = [], array $conditions = []): array
	{
		$queryBuilder = $this->buildQuery('*', $conditions, [], '', $orderBy);

		if (!empty($limit))
			$queryBuilder->setFirstResult($limit['first'])->setMaxResults($limit['max']);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * Finds a single value by a custom WHERE clause.
	 *
	 * @throws Exception
	 */
	public function findOneValueBy(string $field, array $conditions = [], array $joins = [], string $groupBy = '',
								   array $orderBy = []): string
	{
		$queryBuilder = $this->buildQuery($field, $conditions, $joins, $groupBy, $orderBy);

		return $queryBuilder->fetchOne() ?? '';
	}

	/**
	 * @param list<array<string,mixed>> $result
	 * @return array<string, mixed>|array<empty,empty>
	 */
	public function getFirstDataSet(array $result): array
	{
		if (!empty($result) && array_key_exists(0, $result))
			return $result[0];

		return [];
	}

	/**
	 * @param array<string,mixed> $conditions
	 * @param array<string,mixed> $joins
	 * @param list<array<string,mixed>> $orderBy
	 */
	private function buildQuery(string $field, array $conditions, array $joins, string $groupBy = '', array $orderBy = []):
	QueryBuilder
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select($field)->from($this->table);

		$this->determineLeftJoins($queryBuilder, $joins);
		$this->determineConditions($queryBuilder, $conditions);

		if (!empty($groupBy))
			$queryBuilder->groupBy($groupBy);

		foreach ($orderBy as $order)
		{
			if (!empty($order))
				$queryBuilder->addOrderBy($order['sort'], $order['order'] ?? 'ASC');
		}

		return $queryBuilder;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function generateWhereClause(int|string $value, string $compare = '=', string $logic = 'AND', ArrayParameterType $type = ArrayParameterType::INTEGER): array
	{
		return ['value' => $value, 'compare' => $compare, 'logic' => $logic, 'type' => $type];
	}

	/**
	 * @param array<string,mixed> $joins
	 */
	protected function determineLeftJoins(QueryBuilder $queryBuilder, array $joins): void
	{
		foreach ($joins as $table => $onCondition)
		{
			$queryBuilder->leftJoin($this->table, $table, $table, $onCondition);
		}
	}

	public function determineLimit(int $first = 0, int $max = 0): array
	{
		if ($first == 0)
			$first = 1;

		if ($max > 0)
			return ['first' => ($first - 1) * $max,	'max' => $max];

		return [];
	}
}
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

namespace App\Framework\Database\BaseRepositories;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;

trait FindOperationsTrait
{

	protected string $table;

	/**
	 * Finds a record by ID.
	 *
	 * @param int|string $id Record ID
	 * @return array Record data
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
	 * @throws Exception
	 */
	public function findAllBy(array $conditions = [], array $joins = [], int $limitStart = null, int $limitShow =
	null, string $groupBy = '', string $orderBy = ''): array
	{
		return $this->findAllByWithFields(array('*'), $conditions, $joins, $limitStart, $limitShow, $groupBy, $orderBy);
	}

	/**
	 * Finds records with specific fields and a custom WHERE clause.
	 * @throws Exception
	 */
	public function findAllByWithFields(array $fields, array $conditions = [],array $joins = [], int $limitStart = null, int $limitShow = null, string $groupBy = '', string $orderBy = ''): array
	{
		$fields       = implode(', ', $fields);
		$queryBuilder = $this->buildQuery($fields, $conditions, $joins,	$groupBy, $orderBy);

		if ($limitStart !== null && $limitShow !== null)
			$queryBuilder->setFirstResult($limitStart)->setMaxResults($limitShow);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * Finds records with limits and sorting.
	 *
	 * @throws Exception
	 */
	public function findAllByWithLimits(int $limitStart, int $limitShow, string $orderBy, array $conditions = []): array
	{
		$queryBuilder = $this->buildQuery('*', $conditions, [], '', $orderBy);

		$queryBuilder->setFirstResult($limitStart)->setMaxResults($limitShow);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * Finds a single value by a custom WHERE clause.
	 *
	 * @throws Exception
	 */
	public function findOneValueBy(string $field, array $conditions = [], array $joins = [], string $groupBy = '',
								   string $orderBy = ''): string
	{
		$queryBuilder = $this->buildQuery($field, $conditions, $joins, $groupBy, $orderBy);

		return $queryBuilder->fetchOne() ?? '';
	}

	public function getFirstDataSet(array $set)
	{
		if (!empty($set) && array_key_exists(0, $set))
		{
			return $set[0];
		}
		return array();
	}

	private function buildQuery(string $field, array $conditions, array $joins, string $groupBy = '', string $orderBy = ''):
	QueryBuilder
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select($field)->from($this->table);

		$this->determineLeftJoins($queryBuilder, $joins);
		$this->determineConditions($queryBuilder, $conditions);

		if (!empty($groupBy))
			$queryBuilder->groupBy($groupBy);

		if (!empty($orderBy))
			$queryBuilder->orderBy($orderBy);

		return $queryBuilder;
	}


}
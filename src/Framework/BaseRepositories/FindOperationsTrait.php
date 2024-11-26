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

namespace App\Framework\BaseRepositories;

use Doctrine\DBAL\Exception;

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
		$queryBuilder->select('COUNT(1)')
			->from($this->table)
			->where($this->idField . ' = :id')
			->setParameter('id', $id);
		;

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
	public function countAllBy(array $conditions = [], array $join = [], string $groupBy = ''): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('COUNT(1)')->from($this->table);

		if (!empty($groupBy))
			$queryBuilder->groupBy($groupBy);

		foreach ($join as $table => $onCondition)
		{
			$queryBuilder->join($this->table, $table, $table, $onCondition);
		}

		foreach ($conditions as $field => $value)
		{
			$queryBuilder->andWhere("$field = :$field");
			$queryBuilder->setParameter($field, $value);
		}

		return (int) $queryBuilder->executeQuery()->fetchOne();
	}

	/**
	 * Finds records with a custom WHERE clause.
	 *
	 */
	public function findAllBy(array $conditions = [], array $join = [], int $limitStart = null,	int $limitShow = null, string $groupBy = '', string $orderBy = ''): array
	{
		return $this->findAllByWithFields(array('*'), $conditions, $join, $limitStart, $limitShow, $groupBy, $orderBy);
	}

	/**
	 * Finds records with specific fields and a custom WHERE clause.
	 */
	public function findAllByWithFields(array $fields, array $conditions = [],array $join = [], int $limitStart = null, int $limitShow = null, string $groupBy = '', string $orderBy = ''): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select(implode(', ', $fields))->from($this->table);

		if (!empty($groupBy))
			$queryBuilder->groupBy($groupBy);

		if (!empty($orderBy))
			$queryBuilder->orderBy($orderBy);

		foreach ($join as $table => $onCondition)
		{
			$queryBuilder->join($this->table, $table, $table, $onCondition);
		}

		foreach ($conditions as $field => $value)
		{
			$queryBuilder->andWhere("$field = :$field");
			$queryBuilder->setParameter($field, $value);
		}

		if ($limitStart !== null && $limitShow !== null)
			$queryBuilder->setFirstResult($limitStart)->setMaxResults($limitShow);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * Finds records with limits and sorting.
	 *
	 * @throws Exception
	 */
	public function findAllByWithLimits(int $limitStart, int $limitShow, string $sortColumn, string $sortOrder, array
	$whereConditions = []): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();

		$queryBuilder->select('*')->from($this->table);

		if (!empty($orderBy))
			$queryBuilder->orderBy($orderBy);

		foreach ($whereConditions as $field => $value)
		{
			$queryBuilder->andWhere("$field = :$field");
			$queryBuilder->setParameter($field, $value);
		}

		if ($limitStart !== null && $limitShow !== null)
			$queryBuilder->setFirstResult($limitStart)->setMaxResults($limitShow);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

	/**
	 * Finds a single value by a custom WHERE clause.
	 *
	 * @throws Exception
	 */
	public function findOneValueBy(
		string $field,
		array $conditions = [],
		array $join = [],
		string $groupBy = '',
		string $orderBy = ''
	): string {
		$queryBuilder = $this->connection->createQueryBuilder();

		$queryBuilder->select($field)->from($this->table);

		if (!empty($groupBy))
			$queryBuilder->groupBy($groupBy);

		if (!empty($orderBy))
			$queryBuilder->orderBy($orderBy);

		foreach ($join as $table => $onCondition)
		{
			$queryBuilder->join($this->table, $table, $table, $onCondition);
		}

		foreach ($conditions as $column => $value)
		{
			$queryBuilder->andWhere("$column = :$column");
			$queryBuilder->setParameter($column, $value);
		}
		return $queryBuilder->fetchOne() ?? '';
	}

	/**
	 * Gets the first dataset from an array of datasets.
	 *
	 * @param array $ar_set Array of datasets
	 * @return array First dataset
	 */
	protected function getFirstDataSet(array $ar_set): array
	{
		if (!empty($ar_set))
			return $ar_set[0];

		return array();
	}

}
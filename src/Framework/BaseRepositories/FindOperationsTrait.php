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

trait FindOperationsTrait
{

	protected string $table;

	/**
	 * Finds a record by ID.
	 *
	 * @param int|string $id Record ID
	 * @return array Record data
	 */
	public function findById(int|string $id): array
	{
		if (empty($id))
			return array();

		$sql = $this->queryBuilder->buildSelectQuery(
			$this->table,
			'*',
			$this->idField . '=' . $this->dbh->escapeString($id),
			'',
			1
		);
		$result = $this->dbh->select($sql);

		return $this->getFirstDataSet($result);
	}

	/**
	 * Counts all records in the table.
	 *
	 * @return int Number of records
	 */
	public function countAll(): int
	{
		$sql = $this->queryBuilder->buildSelectQuery('COUNT(1)', $this->table);
		return (int) $this->dbh->getSingleValue($sql);
	}

	/**
	 * Counts records in the table with a custom WHERE clause.
	 *
	 * @param string $where WHERE clause
	 * @param string $join JOIN clause
	 * @param string $group_by GROUP BY clause
	 * @return int Number of records
	 */
	public function countAllBy(string $where = '', string $join = '', string $group_by = ''): int
	{
		$sql = $this->queryBuilder->buildSelectQuery(
			'COUNT(1)', $where, $this->table,	$join,'',$group_by
		);

		return (int) $this->dbh->getSingleValue($sql);
	}

	/**
	 * Finds records with a custom WHERE clause.
	 *
	 * @param string $where WHERE clause
	 * @param string $join JOIN clause
	 * @param string $limit LIMIT clause
	 * @param string $group_by GROUP BY clause
	 * @param string $order_by ORDER BY clause
	 * @return array Records data
	 */
	public function findAllBy(string $where = '', string $join = '', string $limit = '', string $group_by = '', string $order_by = ''): array
	{
		return $this->findAllByWithFields('*', $where, $join, $limit, $group_by, $order_by);
	}

	/**
	 * Finds records with specific fields and a custom WHERE clause.
	 *
	 * @param string $fields Fields to select
	 * @param string $where WHERE clause
	 * @param string $join JOIN clause
	 * @param string $limit LIMIT clause
	 * @param string $group_by GROUP BY clause
	 * @param string $order_by ORDER BY clause
	 * @return array Records data
	 */
	public function findAllByWithFields(string $fields, string $where = '', string $join = '', string $limit = '', string $group_by = '', string $order_by = ''): array
	{
		$sql = $this->queryBuilder->buildSelectQuery($fields, $this->table, $where, $join, $limit, $group_by, $order_by);
		return $this->dbh->select($sql);
	}

	/**
	 * Finds records with limits and sorting.
	 *
	 * @param int $limit_start Start limit
	 * @param int $limit_show Number of records to show
	 * @param string $sort_column Column to sort by
	 * @param string $sort_order Sort order
	 * @param string $where WHERE clause
	 * @return array Records data
	 */
	public function findAllByWithLimits(int $limit_start, int $limit_show, string $sort_column, string $sort_order, string $where = ''): array
	{
		$limit    = $this->queryBuilder->buildLimitClause($limit_start, $limit_show);
		$order_by = $this->table.'.'.$sort_column. ' '.$sort_order;
		$sql      = $this->queryBuilder->buildSelectQuery(
			'*', $this->table, $where, '', $limit, '', $order_by
		);

		return $this->dbh->select($sql);
	}

	/**
	 * Finds a single value by a custom WHERE clause.
	 *
	 * @param string $field Field to select
	 * @param string $where WHERE clause
	 * @param string $join JOIN clause
	 * @param string $group_by GROUP BY clause
	 * @param string $order_by ORDER BY clause
	 * @return string Single value
	 */
	public function findOneValueBy(string $field, string $where, string $join = '', string $group_by = '', string $order_by = ''): string
	{
		$sql = $this->queryBuilder->buildSelectQuery($field, $this->table, $where, $join, '', $group_by, $order_by);

		return $this->dbh->getSingleValue($sql);
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
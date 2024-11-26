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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Abstract class Base
 *
 * Provides a base model for database operations.
 */
abstract class Sql
{

	use TransactionTrait;
	use FindOperationsTrait;

	protected string $idField;
	protected Connection $connection;

	public function __construct(Connection $connection, string $table, string $idField)
	{
		$this->connection   = $connection;
		$this->table        = $table;
		$this->idField     = $idField;
	}


	public function getTable(): string
	{
		return $this->table;
	}

	public function getIdField(): string
	{
		return $this->idField;
	}

	/**
	 * Inserts a new record into the database.
	 *
	 * @param array $fields Fields to insert
	 * @return int Inserted record ID
	 * @throws Exception
	 */
	public function insert(array $fields): int
	{
		$this->connection->insert($this->getTable(), $fields);
		return (int)$this->connection->lastInsertId();
	}


	/**
	 * Updates a record in the database by ID.
	 *
	 * @param int|string $id Record ID
	 * @param array $fields Fields to update
	 *
	 * @return int Number of affected rows
	 * @throws Exception
	 */
	public function update(int|string $id, array $fields): int
	{
		return $this->connection->update($this->getTable(), $fields,[$this->getIdField() => $id]);
	}

	/**
	 * Updates records in the database with a custom WHERE clause.
	 *
	 * @param array $fields Fields to update
	 * @param array $conditions
	 * @return int Number of affected rows
	 * @throws Exception
	 */
	public function updateWithWhere(array $fields, array $conditions): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->getTable());

		foreach ($fields as $field => $value)
		{
			$queryBuilder->set($field, ":set_$field");
			$queryBuilder->setParameter("set_$field", $value);
		}

		foreach ($conditions as $field => $value)
		{
			$queryBuilder->andWhere("$field = :cond_$field");
			$queryBuilder->setParameter("cond_$field", $value);
		}

		return $queryBuilder->executeStatement();
	}

	/**
	 * Deletes a record from the database by ID.
	 * @throws Exception
	 */
	public function delete(int|string $id): int
	{
		return $this->connection->delete($this->getTable(), [$this->getIdField() => $id]);
	}

	/**
	 * Deletes records from the database by a specific field.
	 *
	 * @throws Exception
	 */
	public function deleteByField(string $field, mixed $value): int
	{
		return $this->connection->delete($this->getTable(), [$field => $value]);
	}

	/**
	 * Deletes records from the database with a custom WHERE clause.
	 * @throws Exception
	 */
	public function deleteBy(array $conditions): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->delete($this->getTable());

		foreach ($conditions as $field => $value)
		{
			$queryBuilder->andWhere("$field = :$field");
			$queryBuilder->setParameter($field, $value);
		}

		return $queryBuilder->executeStatement();
	}

	/**
	 * Shows columns of the table.
	 *
	 * @return array Columns data
	 * @throws Exception
	 */
	public function showColumns(): array
	{
		return $this->connection->createSchemaManager()->listTableColumns($this->getTable());
	}

	/**
	 * @throws Exception
	 */
	public function showTables(): array
	{
		return $this->connection->createSchemaManager()->listTables();
	}

	protected function determineConditions(array $conditions, QueryBuilder $queryBuilder): void
	{
		foreach ($conditions as $field => $value)
		{
			$queryBuilder->andWhere("$field = :$field");
			$queryBuilder->setParameter($field, $value);
		}
	}


}
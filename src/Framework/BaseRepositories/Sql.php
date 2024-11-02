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

use App\Framework\BaseRepositories\SqlHelperTraits\FindOperationsTrait;
use App\Framework\BaseRepositories\SqlHelperTraits\TransactionTrait;
use App\Framework\Database\DBHandler;
use App\Framework\Database\Helpers\DataPreparer;
use App\Framework\Database\QueryBuilder;

/**
 * Abstract class Base
 *
 * Provides a base model for database operations.
 */
abstract class Sql
{

	use TransactionTrait;
	use FindOperationsTrait;

	/**
	 * @var DBHandler Database handler
	 */
	protected DBHandler $dbh;
	/**
	 * @var QueryBuilder
	 */
	protected QueryBuilder $QueryBuilder;
	/**
	 * @var DataPreparer
	 */
	protected DataPreparer $DataPreparer;

	/**
	 * Constructor
	 *
	 * @param DBHandler $dbh Database handler
	 * @param string $table Table name
	 * @param string $id_field ID field name
	 */
	public function __construct(DBHandler $dbh, QueryBuilder $queryBuilder, DataPreparer $dataPreparer, string $table, string $id_field)
	{
		$this->dbh          = $dbh;
	 	$this->QueryBuilder = $queryBuilder;
		$this->table        = $table;
		$this->id_field     = $id_field;
	}

	/**
	 * Gets the database handler.
	 *
	 * @return DBHandler
	 */
	public function getDbh(): DBHandler
	{
		return $this->dbh;
	}


	public function getDataPreparer(): DataPreparer
	{
		return $this->DataPreparer;
	}

	/**
	 * Gets the table name.
	 *
	 * @return string
	 */
	public function getTable(): string
	{
		return $this->table;
	}

	/**
	 * Sets the ID field name.
	 *
	 * @param string $id_field ID field name
	 * @return $this
	 */
	protected function setIdField(string $id_field): Sql
	{
		$this->id_field = $id_field;
		return $this;
	}

	/**
	 * Gets the ID field name.
	 *
	 * @return string
	 */
	public function getIdField(): string
	{
		return $this->id_field;
	}

	/**
	 * Inserts a new record into the database.
	 *
	 * @param array $fields Fields to insert
	 * @return int Inserted record ID
	 */
	public function insert(array $fields): int
	{
		$sql = $this->QueryBuilder->buildInsertQuery($this->getTable(), $this->getDataPreparer()->prepareForDB($fields));
		return $this->getDbh()->insert($sql);
	}

	/**
	 * Updates a record in the database by ID.
	 *
	 * @param int|string $id Record ID
	 * @param array $ar_fields Fields to update
	 * @return int Number of affected rows
	 */
	public function update(int|string $id, array $ar_fields): int
	{
		// this is required because the id field can be a string or an integer
		$id_prepare = array($this->getIdField() => $id);
		$id_cleaned = $this->getDataPreparer()->prepareForDB($id_prepare);
		$quoted_id  = $id_cleaned[$this->getIdField()];

		$sql = $this->QueryBuilder->buildUpdateQuery(
			$this->table,
			$this->getDataPreparer()->prepareForDB($ar_fields),
			$this->getIdField() . ' = '. $quoted_id
		);

		return $this->getDbh()->update($sql);
	}

	/**
	 * Updates records in the database with a custom WHERE clause.
	 *
	 * @param array $fields Fields to update
	 * @param mixed $where WHERE clause
	 * @return int Number of affected rows
	 */
	public function updateWithWhere(array $fields, string $where): int
	{
		$sql = $this->QueryBuilder->buildUpdateQuery(
			$this->table,
			$this->getDataPreparer()->prepareForDB($fields),
			$where
		);

		return $this->getDbh()->update($sql);
	}

	/**
	 * Deletes a record from the database by ID.
	 *
	 * @param int|string $id Record ID
	 * @param mixed $limit Limit for deletion
	 * @return int
	 */
	public function delete(int|string $id, string $limit = null): int
	{
		$where = $this->getIdField() . '=' . $id;
		return $this->deleteBy($where, $limit);
	}

	/**
	 * Deletes records from the database by a specific field.
	 *
	 * @param string $field Field name
	 * @param mixed $value Field value
	 * @param string $limit Limit for deletion
	 * @return int
	 */
	public function deleteByField(string $field, mixed $value, string $limit = ''): int
	{
		$where = $field . '=' . $value;
		return $this->deleteBy($where, $limit);
	}

	/**
	 * Deletes records from the database with a custom WHERE clause.
	 *
	 * @param string $where WHERE clause
	 * @param string $limit Limit for deletion
	 * @return int
	 */
	public function deleteBy(string $where, string $limit = ''): int
	{
		$sql = $this->QueryBuilder->buildDeleteQuery(
			$this->getTable(),
			$where,
			$limit
		);
		return $this->getDbh()->delete($sql);
	}

	/**
	 * Shows columns of the table.
	 *
	 * @return array Columns data
	 */
	public function showColumns(): array
	{
		return $this->getDbh()->show('COLUMNS', $this->getTable());
	}

}
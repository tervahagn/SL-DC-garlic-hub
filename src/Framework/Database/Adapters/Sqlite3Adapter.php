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

namespace App\Framework\Database\Adapters;

use App\Framework\Exceptions\DatabaseException;
use SQLite3;

/**
 * Class Sqlite3Adapter
 *
 * Adapter for connecting to an SQLite database using SQLite3, implementing DatabaseAdapterInterface.
 */
class Sqlite3Adapter implements AdapterInterface
{
	/**
	 * @var SQLite3
	 */
	private SQLite3 $db;
	/**
	 * @var bool
	 */
	private bool  $is_transaction = false;
	/**
	 * @var mixed
	 */
	private mixed $result;

	/**
	 * Establishes a connection to the SQLite database.
	 *
	 * @param array $credentials
	 * @throws DatabaseException
	 */
	public function connect(array $credentials): void
	{
		if (!isset($credentials['db_path']))
			throw new DatabaseException("Database path not provided for SQLite.");

		try
		{
			$this->db = new SQLite3($credentials['db_path']);
		}
		catch (\Exception $e)
		{
			throw new DatabaseException("Connection failed: " . $e->getMessage(), (int)$e->getCode());
		}
	}

	/**
	 * Returns connection data (Driver, Host, and Database Name).
	 *
	 * @return array
	 */
	public function getConnectionData(): array
	{
		return [
			'host' => 'SQLite does not use host',
			'db_name' => $this->db->querySingle("PRAGMA database_list", true)['name'] ?? '',
			'db_driver' => 'sqlite3'
		];
	}

	/**
	 * @param string $sql
	 *
	 * @return int
	 * @throws DatabaseException
	 */
	public function insert(string $sql): int
	{
		$this->executeQuery($sql);
		return $this->db->lastInsertRowID();
	}

	/**
	 * @param string $sql
	 *
	 * @return int
	 * @throws DatabaseException
	 */
	public function update(string $sql): int
	{
		$this->executeQuery($sql);
		return $this->db->changes();
	}

	/**
	 * @param string $sql
	 *
	 * @return int
	 * @throws DatabaseException
	 */
	public function delete(string $sql): int
	{
		$this->executeQuery($sql);
		return $this->db->changes();
	}

	/**
	 * @param string $sql
	 *
	 * @return array
	 * @throws DatabaseException
	 */
	public function select(string $sql): array
	{
		$this->executeQuery($sql);
		return $this->fetchRows();
	}

	/**
	 * @throws DatabaseException
	 */
	public function getSingleValue(string $sql, int|string $offset = 0): array|int|string
	{
		$this->executeQuery($sql);
		$row = $this->result->fetchArray(SQLITE3_NUM);

		if ($this->result && isset($row[$offset]))
			return $row[$offset];

		return '';
	}

	/**
	 * @throws DatabaseException
	 */
	public function show(string $what= 'TABLES', string $table_name = ''): array
	{
		if (strtoupper($what) === 'COLUMNS')
		{
			if (!empty($table_name))
				throw new DatabaseException('Missing argument table_name');

			$sql = 'PRAGMA table_info (' . $this->escapeString($table_name) . ')';
		}
		else if (strtoupper($what) === 'TABLES')
		{
			$sql = "SELECT name FROM sqlite_master";
			if (!empty($table_name))
				$sql .= " WHERE type='table' AND name='" . $table_name . "'";
		}
		else
			throw new DatabaseException("Invalid argument for show(): " . $what);

		return $this->select($sql);
	}

	/**
	 * Begins a transaction.
	 *
	 * @throws DatabaseException
	 */
	public function beginTransaction(): void
	{
		if ($this->is_transaction) {
			return;
		}

		if (!$this->db->exec('BEGIN TRANSACTION')) {
			throw new DatabaseException("Failed to begin transaction: " . $this->db->lastErrorMsg());
		}

		$this->is_transaction = true;
	}

	/**
	 * Commits a transaction.
	 *
	 * @throws DatabaseException
	 */
	public function commitTransaction(): void
	{
		if (!$this->db->exec('COMMIT')) {
			throw new DatabaseException("Failed to commit transaction: " . $this->db->lastErrorMsg());
		}

		$this->is_transaction = false;
	}

	/**
	 * Rolls back a transaction.
	 *
	 * @throws DatabaseException
	 */
	public function rollbackTransaction(): void
	{
		if (!$this->db->exec('ROLLBACK'))
			throw new DatabaseException("Failed to rollback transaction: " . $this->db->lastErrorMsg());

		$this->is_transaction = false;
	}

	public function hasActiveTransaction(): bool
	{
		return $this->is_transaction;
	}

	/**
	 * Escapes an unsafe string.
	 *
	 * @param string $unsafe
	 *
	 * @return string
	 */
	public function escapeString(string $unsafe): string
	{
		return $this->db->escapeString($unsafe);
	}

	/**
	 * Executes a query.
	 *
	 * @param string $sql
	 * @throws DatabaseException
	 */
	public function executeQuery(string $sql): void
	{
		$this->result = $this->db->query($sql);
		if (!$this->result)
			throw new DatabaseException("Query failed: " . $this->db->lastErrorMsg(), $this->db->lastErrorCode());
	}

	/**
	 * Returns all rows from the last result set.
	 *
	 * @return array
	 */
	private function fetchRows(): array
	{
		$rows = [];
		while ($row = $this->result->fetchArray(SQLITE3_ASSOC))
		{
			$rows[] = $row;
		}
		return $rows;
	}

}

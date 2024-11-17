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
use mysqli;

/**
 * Class MySqliAdapter
 *
 * Adapter for connecting to the database using MySQLi, implementing DatabaseAdapterInterface.
 */
class MySqliAdapter implements AdapterInterface
{
	/**
	 * @var mysqli
	 */
	private mysqli $db;
	/**
	 * @var bool
	 */
	private bool  $isTransaction = false;
	/**
	 * @var mixed
	 */
	private mixed $result;

	/**
	 * Establishes a connection to the database.
	 *
	 * @param array $credentials
	 * @throws DatabaseException
	 */
	public function connect(array $credentials): void
	{
		$this->db = new mysqli($credentials['db_host'], $credentials['db_user'], $credentials['db_pass'], $credentials['db_name'], $credentials['db_port']);
		if ($this->db->connect_errno)
			throw new DatabaseException("Connection failed: " . $this->db->connect_error, $this->db->connect_errno);

		$this->db->set_charset('utf8');
	}

	/**
	 * Returns connection data (Driver, Host, and Database Name).
	 *
	 * @return array
	 */
	public function getConnectionData(): array
	{
		return [
			'host' => $this->db->host_info,
			'db_name' => $this->db->query("SELECT DATABASE()")->fetch_row()[0],
			'db_driver' => 'mysqli'
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
		return $this->db->insert_id;

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
		return $this->db->affected_rows;
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
		return $this->db->affected_rows;
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
	public function getSingleValue(string $sql, int|string $offset = 0): mixed
	{
		$row = $this->select($sql);

		if($this->result->num_rows > 0 && array_key_exists($offset, $row))
			return $row[$offset];

		return '';
	}


	/**
	 * @throws DatabaseException
	 */
	public function show(string $what= 'TABLES', string $table = ''): array
	{
		if (strtoupper($what) === 'COLUMNS')
		{
			if (!empty($table))
				throw new DatabaseException('Missing argument table_name');

			$sql = 'SHOW COLUMNS FROM ' . $this->escapeString($table) . ')';
		}
		else if (strtoupper($what) === 'TABLES')
		{
			$sql = 'SHOW TABLES';
			if (!empty($table))
				$sql .= " WHERE type='table' AND name='" . $table . "'";
		}
		else
			throw new DatabaseException("Invalid argument for show(): " . $what);

		return $this->select($sql);
	}


	/**
	 * Escapes an unsafe string.
	 *
	 * @param string $unsafe
	 * @return string
	 */
	public function escapeString(string $unsafe): string
	{
		return $this->db->real_escape_string($unsafe);
	}

	/**
	 * Begins a transaction.
	 *
	 * @throws DatabaseException
	 */
	public function beginTransaction(): void
	{
		if ($this->isTransaction)
			return;

		if (!$this->db->autocommit(false))
			throw new DatabaseException("Failed to begin transaction: " . $this->db->error, $this->db->errno);

		$this->isTransaction = true;
	}

	/**
	 * Commits a transaction.
	 *
	 * @throws DatabaseException
	 */
	public function commitTransaction(): void
	{
		if (!$this->db->commit())
			throw new DatabaseException("Failed to commit transaction: " . $this->db->error, $this->db->errno);

		$this->db->autocommit(true);
		$this->isTransaction = false;
	}

	/**
	 * Rolls back a transaction.
	 *
	 * @throws DatabaseException
	 */
	public function rollbackTransaction(): void
	{
		if (!$this->db->rollback())
			throw new DatabaseException("Failed to rollback transaction: " . $this->db->error, $this->db->errno);

		$this->db->autocommit(true);
		$this->isTransaction = false;
	}

	public function hasActiveTransaction(): bool
	{
		return $this->isTransaction;
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
			throw new DatabaseException("Query failed: " . $this->db->error, $this->db->errno);
	}

	/**
	 * Returns all rows from the last result set.
	 *
	 * @return array
	 */
	private function fetchRows(): array
	{
		$rows = [];
		while ($row = $this->result->fetch_assoc())
		{
			$rows[] = $row;
		}
		return $rows;
	}

}

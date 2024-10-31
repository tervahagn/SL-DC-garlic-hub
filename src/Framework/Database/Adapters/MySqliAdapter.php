<?php

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
	private bool  $is_transaction = false;
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

	public function getSingleValue(string $sql, int|string $offset = 0): mixed
	{
		$row = $this->select($sql);

		if($this->result->num_rows > 0 && array_key_exists($offset, $row))
			return $row[$offset];

		return '';
	}


	public function show(string $what= 'TABLES', string $table_name = ''): array
	{
		if (strtoupper($what) === 'COLUMNS')
		{
			if (!empty($table_name))
				throw new DatabaseException('Missing argument table_name');

			$sql = 'SHOW COLUMNS FROM ' . $this->escapeString($table_name) . ')';
		}
		else if (strtoupper($what) === 'TABLES')
		{
			$sql = 'SHOW TABLES';
			if (!empty($table_name))
				$sql .= " WHERE type='table' AND name='" . $table_name . "'";
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
		if ($this->is_transaction)
			return;

		if (!$this->db->autocommit(false))
			throw new DatabaseException("Failed to begin transaction: " . $this->db->error, $this->db->errno);

		$this->is_transaction = true;
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
		$this->is_transaction = false;
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
		$this->is_transaction = false;
	}

	public function hasActiveTransaction(): bool
	{
		return $this->is_transaction;
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

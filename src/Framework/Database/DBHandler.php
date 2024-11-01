<?php

namespace App\Framework\Database;

use App\Framework\Database\Adapters\AdapterInterface;

/**
 * Handles database operations through a specified adapter.
 */
class DBHandler
{
	/** @var AdapterInterface Database adapter for executing queries */
	private AdapterInterface $adapter;

	/**
	 * @param AdapterInterface $adapter The adapter to handle database interactions.
	 */
	public function __construct(AdapterInterface $adapter)
	{
		$this->adapter = $adapter;
	}

	/**
	 * Connects to the database with given credentials.
	 *
	 * @param array $credentials Connection details
	 */
	public function connect(array $credentials): void
	{
		$this->adapter->connect($credentials);
	}

	/**
	 * Executes an insert query.
	 *
	 * @param string $sql Insert statement
	 * @return int Number of affected rows
	 */
	public function insert(string $sql): int
	{
		return $this->adapter->insert($sql);
	}

	/**
	 * Executes an update query.
	 *
	 * @param string $sql Update statement
	 * @return int Number of affected rows
	 */
	public function update(string $sql): int
	{
		return $this->adapter->update($sql);
	}

	/**
	 * Executes a delete query.
	 *
	 * @param string $sql Delete statement
	 * @return int Number of affected rows
	 */
	public function delete(string $sql): int
	{
		return $this->adapter->delete($sql);
	}

	/**
	 * Executes a select query and returns the result set.
	 *
	 * @param string $sql Select statement
	 * @return array Result set
	 */
	public function select(string $sql): array
	{
		return $this->adapter->select($sql);
	}

	/**
	 * Retrieves a single value from a query result.
	 *
	 * @param string $sql Query statement
	 * @param int|string $offset Optional value offset
	 * @return mixed The selected value
	 */
	public function getSingleValue(string $sql, int|string $offset = 0): mixed
	{
		return $this->adapter->getSingleValue($sql, $offset);
	}

	/**
	 * Displays database details such as tables or columns.
	 *
	 * @param string $what Type of detail to show
	 * @param string $table_name Optional table name
	 * @return array Result set
	 */
	public function show(string $what = 'TABLES', string $table_name = ''): array
	{
		return $this->adapter->show($what, $table_name);
	}

	/**
	 * Executes a raw SQL query.
	 *
	 * @param string $sql Query statement
	 * @return DBHandler
	 */
	public function executeQuery(string $sql): DBHandler
	{
		$this->adapter->executeQuery($sql);
		return $this;
	}

	/**
	 * Escapes a string for safe use in queries.
	 *
	 * @param string $unsafe String to escape
	 * @return string Escaped string
	 */
	public function escapeString(string $unsafe): string
	{
		return $this->adapter->escapeString($unsafe);
	}

	/**
	 * Retrieves current connection data.
	 *
	 * @return array Connection information
	 */
	public function getConnectionData(): array
	{
		return $this->adapter->getConnectionData();
	}

	/**
	 * Begins a database transaction.
	 */
	public function beginTransaction(): void
	{
		$this->adapter->beginTransaction();
	}

	/**
	 * Commits the current transaction.
	 */
	public function commitTransaction(): void
	{
		$this->adapter->commitTransaction();
	}

	/**
	 * Rolls back the current transaction.
	 */
	public function rollbackTransaction(): void
	{
		$this->adapter->rollbackTransaction();
	}
}

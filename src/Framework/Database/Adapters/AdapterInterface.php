<?php

namespace App\Framework\Database\Adapters;

/**
 * Defines database adapter methods for CRUD operations and transaction management.
 */
interface AdapterInterface
{
	/**
	 * Establishes a database connection.
	 *
	 * @param array $credentials Connection details
	 */
	public function connect(array $credentials): void;

	/**
	 * Retrieves the current connection data.
	 *
	 * @return array
	 */
	public function getConnectionData(): array;

	/**
	 * Executes an insert query.
	 *
	 * @param string $sql Insert statement
	 * @return int Number of affected rows
	 */
	public function insert(string $sql): int;

	/**
	 * Executes an update query.
	 *
	 * @param string $sql Update statement
	 * @return int Number of affected rows
	 */
	public function update(string $sql): int;

	/**
	 * Executes a delete query.
	 *
	 * @param string $sql Delete statement
	 * @return int Number of affected rows
	 */
	public function delete(string $sql): int;

	/**
	 * Executes a select query and returns the result set.
	 *
	 * @param string $sql Select statement
	 * @return array Result set
	 */
	public function select(string $sql): array;

	/**
	 * Retrieves a single value from a result set.
	 *
	 * @param string $sql Query statement
	 * @param int|string $offset Optional value offset
	 * @return mixed The selected value
	 */
	public function getSingleValue(string $sql, int|string $offset = 0): mixed;

	/**
	 * Displays database details (e.g., tables, columns).
	 *
	 * @param string $what Type of detail to show
	 * @param string $table_name Optional table name
	 * @return array Result set
	 */
	public function show(string $what= 'TABLES', string $table_name = ''): array;

	/**
	 * Executes a raw SQL query.
	 *
	 * @param string $sql Query statement
	 */
	public function executeQuery(string $sql): void;

	/**
	 * Escapes a string for safe query usage.
	 *
	 * @param string $unsafe String to escape
	 * @return string Escaped string
	 */
	public function escapeString(string $unsafe): string;

	/**
	 * Checks if a transaction is active.
	 *
	 * @return bool
	 */
	public function hasActiveTransaction(): bool;

	/**
	 * Starts a database transaction.
	 */
	public function beginTransaction(): void;

	/**
	 * Commits the current transaction.
	 */
	public function commitTransaction(): void;

	/**
	 * Rolls back the current transaction.
	 */
	public function rollbackTransaction(): void;
}
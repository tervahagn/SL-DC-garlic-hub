<?php

namespace App\Framework\Database\Adapters;

use App\Framework\Exceptions\DatabaseException;
use PDO;
// Only for Testing. We prefer to use the native apis
/**
 *
 * Class PdoAdapter
 *
 * Adapter for connecting to the database using PDO, implementing the DatabaseAdapterInterface.
 */
class PdoAdapter implements AdapterInterface
{
	/**
	 * @var PDO
	 */
	private PDO   $db;
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
		try
		{
			$dsn = $this->determineDSN($credentials);
			$this->db = new PDO($dsn, $credentials['user'] ?? null, $credentials['password'] ?? null);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (\PDOException $e)
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
		$driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
		$name = 'sqlite has no db name';
		$host = 'sqlite has no host';

		if ($driver !== 'sqlite') {
			$host = $this->db->getAttribute(PDO::ATTR_SERVER_INFO);
			$name = $this->db->query('SELECT database()')->fetchColumn();
		}

		return [
			'host' => $host,
			'db_name' => $name,
			'db_driver' => $driver
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
		return $this->db->lastInsertId();
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
		return $this->result->affected_rows();
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
		return $this->result->rowCount;
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
		return $this->result->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSingleValue(string $sql, int|string $offset = 0): mixed
	{
		$this->executeQuery($sql);
		$row = $this->result->fetch(PDO::FETCH_NUM);

		if($this->result->rowCount() > 0 && array_key_exists($offset, $row))
			return $row[$offset];

		return '';
	}


	public function show(string $what= 'TABLES', string $table_name = ''): array
	{
		// TODO: Implement showTables() method.
		return array();
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

		if (!$this->db->beginTransaction())
			throw new DatabaseException("Failed to begin transaction.");

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
			throw new DatabaseException("Failed to commit transaction.");

		$this->is_transaction = false;
	}

	/**
	 * Rolls back a transaction.
	 *
	 * @throws DatabaseException
	 */
	public function rollbackTransaction(): void
	{
		if (!$this->db->rollBack())
			throw new DatabaseException("Failed to rollback transaction.");

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
	 * @return string
	 */
	public function escapeString(string $unsafe): string
	{
		return $this->db->quote($unsafe);
	}

	/**
	 * Executes a query.
	 *
	 * @param string $sql
	 * @throws DatabaseException
	 */
	public function executeQuery(string $sql): void
	{
		try
		{
			$this->result = $this->db->query($sql);
		}
		catch (\PDOException $e)
		{
			throw new DatabaseException("Query failed: " . $e->getMessage());
		}
	}

	/**
	 * Determines the DSN based on the parameters.
	 *
	 * @param array $params
	 * @return string
	 * @throws DatabaseException
	 */
	private function determineDSN(array $params): string
	{
		return match($params['db_driver']) {
			'PDO_MYSQL' => "mysql:host={$params['db_host']};port={$params['db_port']};dbname={$params['db_name']};charset=utf8",
			'PDO_PGSQL' => "pgsql:host={$params['db_host']};port={$params['db_port']};dbname={$params['db_name']};charset=utf8",
			'PDO_SQLITE' => "sqlite:{$params['db_path']}",
			default => throw new DatabaseException("Invalid database driver."),
		};
	}
}

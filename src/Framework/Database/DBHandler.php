<?php

namespace App\Framework\Database;

use App\Framework\Database\Adapters\AdapterInterface;

class DBHandler
{
	private AdapterInterface $adapter;

	public function __construct(AdapterInterface $adapter)
	{
		$this->adapter = $adapter;
	}

	public function connect(array $credentials): void
	{
		$this->adapter->connect($credentials);
	}

	public function insert(string $sql): int
	{
		return $this->adapter->insert($sql);
	}

	public function update(string $sql): int
	{
		return $this->adapter->update($sql);
	}

	public function delete(string $sql): int
	{
		return $this->adapter->delete($sql);
	}

	public function select(string $sql): array
	{
		return $this->adapter->select($sql);
	}

	public function getSingleValue(string $sql, int|string $offset = 0): mixed
	{
		return $this->adapter->getSingleValue($sql);
	}

	public function show(string $what= 'TABLES', string $table_name = ''): array
	{
		return $this->adapter->show($what, $table_name);
	}

	public function executeQuery(string $sql): DBHandler
	{
		$this->adapter->executeQuery($sql);
		return $this;
	}

	public function escapeString(string $unsafe): string
	{
		return $this->adapter->escapeString($unsafe);
	}

	public function getConnectionData(): array
	{
		return $this->adapter->getConnectionData();
	}

	public function beginTransaction(): void
	{
		$this->adapter->beginTransaction();
	}

	public function commitTransaction(): void
	{
		$this->adapter->commitTransaction();
	}

	public function rollbackTransaction(): void
	{
		$this->adapter->rollbackTransaction();
	}

}

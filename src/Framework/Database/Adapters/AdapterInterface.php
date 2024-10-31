<?php

namespace App\Framework\Database\Adapters;

interface AdapterInterface
{
	public function connect(array $credentials): void;
	public function getConnectionData(): array;
	public function insert(string $sql): int;
	public function update(string $sql): int;
	public function delete(string $sql): int;
	public function select(string $sql): array;
	public function getSingleValue(string $sql, int|string $offset = 0): mixed;
	public function show(string $what= 'TABLES', string $table_name = ''): array;
	public function executeQuery(string $sql): void;
	public function escapeString(string $unsafe): string;
	public function hasActiveTransaction(): bool;
	public function beginTransaction(): void;
	public function commitTransaction(): void;
	public function rollbackTransaction(): void;
}
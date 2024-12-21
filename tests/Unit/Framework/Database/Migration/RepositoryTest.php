<?php

namespace Tests\Unit\Framework\Database\Migration;

use App\Framework\Database\Migration\Repository;
use App\Framework\Exceptions\DatabaseException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

//Todo: Put this in Integrationtests
class RepositoryTest extends TestCase
{
	private Repository $repository;
	private Connection $connection;

	protected function setUp(): void
	{
		$this->connection = DriverManager::getConnection([
			'driver' => 'pdo_sqlite',
			'memory' => true,
		]);

		// Repository-Instanz initialisieren
		$this->repository = new Repository($this->connection);

		$this->repository->createMigrationTable();
	}

	#[Group('units')]
	public function testCreateMigrationTable(): void
	{
		$tables = $this->repository->showTables();
		$this->assertNotEmpty($tables);

		// Prüfe, ob die Tabelle existiert
		$tableNames = array_map(fn($table) => $table->getName(), $tables);
		$this->assertContains('_migration_version', $tableNames);
	}

	#[Group('units')]
	public function testGetAppliedMigrationsEmpty(): void
	{
		$appliedMigrations = $this->repository->getAppliedMigrations();
		$this->assertEmpty($appliedMigrations, 'Expected no applied migrations in a fresh database.');
	}

	#[Group('units')]
	public function testApplySqlBatch(): void
	{
		$sqlBatch = "
            INSERT INTO _migration_version (version) VALUES (1);
            INSERT INTO _migration_version (version) VALUES (2);
        ";

		$this->repository->applySqlBatch($sqlBatch);

		$appliedMigrations = $this->repository->getAppliedMigrations();
		$this->assertCount(2, $appliedMigrations);

		$versions = array_column($appliedMigrations, 'version');
		$this->assertContains(1, $versions);
		$this->assertContains(2, $versions);
	}

	#[Group('units')]
	public function testApplySqlBatchWithException(): void
	{
		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('An exception occurred while executing a query');

		$sqlBatch = "
            INSERT INTO invalid_table (version) VALUES (1);
        ";

		$this->repository->applySqlBatch($sqlBatch);
	}

	#[Group('units')]
	public function testShowColumns(): void
	{
		$columns = $this->repository->showColumns();
		$this->assertNotEmpty($columns);

		$columnNames = array_keys($columns);
		$this->assertContains('version', $columnNames);
		$this->assertContains('migrated_at', $columnNames);
	}
}

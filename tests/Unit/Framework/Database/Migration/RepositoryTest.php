<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace Tests\Unit\Framework\Database\Migration;

use App\Framework\Database\Migration\Repository;
use App\Framework\Exceptions\DatabaseException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

//Todo: Put this in Integration tests
class RepositoryTest extends TestCase
{
	private Repository $repository;
	private Connection $connection;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->connection = DriverManager::getConnection([
			'driver' => 'pdo_sqlite',
			'memory' => true,
		]);

		// Repository-Instanz initialisieren
		$this->repository = new Repository($this->connection);

		$this->repository->createMigrationTable();
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testCreateMigrationTable(): void
	{
		$tables = $this->repository->showTables();
		$this->assertNotEmpty($tables);

		// Prüfe, ob die Tabelle existiert
		$tableNames = array_map(fn($table) => $table->getName(), $tables);
		$this->assertContains('_migration_version', $tableNames);
	}

	/**
	 * @throws Exception
	 */
	#[Group('units')]
	public function testGetAppliedMigrationsEmpty(): void
	{
		$appliedMigrations = $this->repository->getAppliedMigrations();
		$this->assertEmpty($appliedMigrations, 'Expected no applied migrations in a fresh database.');
	}

	/**
	 * @throws DatabaseException
	 * @throws Exception
	 */
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

	/**
	 * @throws Exception
	 */
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

	/**
	 * @throws Exception
	 */
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

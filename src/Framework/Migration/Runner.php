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

namespace App\Framework\Migration;

use App\Framework\Exceptions\DatabaseException;
use Doctrine\DBAL\Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

class Runner
{
	private Repository $migrateRepository;
	private Filesystem $filesystem;

	public function __construct(Repository $MigrateRepository, Filesystem $filesystem)
	{
		$this->migrateRepository = $MigrateRepository;
		$this->filesystem = $filesystem;
	}

	/**
	 * @param int|null $targetVersion
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws FilesystemException
	 */
	public function execute(int $targetVersion = null): void
	{
		if (!$this->hasMigrationTable())
			$this->migrateRepository->createMigrationTable();

		$appliedMigrations   = $this->getAppliedMigrations();
		$availableMigrations = $this->getAvailableMigrations();

		$targetMigrations = $targetVersion !== null
			? array_filter($availableMigrations, fn($version) => $version <= $targetVersion)
			: $availableMigrations;

		foreach ($targetMigrations as $version => $file)
		{
			if (in_array($version, $appliedMigrations, true))
				continue;

			$this->applyMigration($version, $file);
		}
	}

	/**
	 * @throws Exception
	 */
	public function revertTo(int $targetVersion): void
	{
		$appliedMigrations = $this->getAppliedMigrations();

		foreach (array_reverse($appliedMigrations) as $version)
		{
			if ($version > $targetVersion)
				$this->revertTo($version);

		}
	}

	private function hasMigrationTable(): bool
	{
		return !empty($this->migrateRepository->showTables());
	}

	/**
	 * @param int $version
	 * @param string $file
	 * @throws DatabaseException
	 * @throws Exception
	 * @throws FilesystemException
	 */
	private function applyMigration(int $version, string $file): void
	{
		$sql = $this->filesystem->read($file);
		$this->migrateRepository->applySqlBatch($sql);
		$this->recordMigration($version);
	}

	/**
	 * @throws DatabaseException
	 * @throws FilesystemException
	 * @throws Exception
	 */
	private function revertMigration(int $version): void
	{
		$downFile = sprintf('%03d.down.sql', $version);
		if (!$this->filesystem->fileExists($downFile))
			throw new DatabaseException("Revert file $downFile not found.");

		$this->applyMigration($version, $downFile);
	}

	/**
	 * @throws Exception
	 */
	private function getAppliedMigrations(): array
	{
		return $this->migrateRepository->getAppliedMigrations();
	}

	/**
	 * @throws FilesystemException
	 */
	private function getAvailableMigrations(): array
	{
		$files = $this->filesystem->listContents('', false);

		$migrations = [];
		foreach ($files as $file)
		{
			if ($file->isFile() && preg_match('/^(\d+)\.up\.sql$/', $file->path(), $matches))
				$migrations[(int)$matches[1]] = $file->path();
		}

		ksort($migrations);
		return $migrations;
	}

	/**
	 * @throws Exception
	 */
	private function recordMigration(int $version): void
	{
		$this->migrateRepository->insert(['version' => $version]);
	}

	/**
	 * @throws Exception
	 */
	private function removeMigrationRecord(int $version): void
	{
		$this->migrateRepository->delete($version);
	}

}

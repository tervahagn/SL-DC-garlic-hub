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

namespace App\Framework\Database\Migration;

use App\Framework\Database\DBHandler;
use App\Framework\Database\QueryBuilder;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\DatabaseException;

/**
 * Class MigrateDatabase
 * @package App\Framework\Database\Migration
 */
class MigrateDatabase
{

	const MIGRATION_TABLE_NAME = '_migration_version';

	/**
	 * @var string
	 */
	private string $fieldName = 'version';

	/**
	 * @var DBHandler
	 */
	private DBHandler $dbh;

	/**
	 * @var QueryBuilder
	*/
	private QueryBuilder $QueryBuilder;

	/**
	 * @var integer
	 */
	protected int $version = 0;

	/**
	 * @var string
	 */
	private string $migrationFilePath;

	/**
	 * @var bool
	 */
	private bool $silent_output = false;

	/**
	 * @param DBHandler $dbh
	 */
	public function __construct(DBHandler $dbh, QueryBuilder $queryBuilder)
	{
		$this->setDbh($dbh);
		$this->QueryBuilder = $queryBuilder;
	}

	/**
	 * @return DBHandler
	 */
	public function getDbh(): DBHandler
	{
		return $this->dbh;
	}

	/**
	 * @param DBHandler $dbh
	 * @return $this
	 */
	public function setDbh(DBHandler $dbh): MigrateDatabase
	{
		$this->dbh = $dbh;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMigrationFilePath(): string
	{
		return $this->migrationFilePath;
	}

	/**
	 * @param string $migrationFilePath
	 *
	 * @return $this
	 */
	public function setMigrationFilePath(string $migrationFilePath): MigrateDatabase
	{
		$this->migrationFilePath = $migrationFilePath;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSilentOutput(): bool
	{
		return $this->silent_output;
	}

	/**
	 * @param boolean $silent_output
	 *
	 * @return $this
	 */
	public function setSilentOutput(bool $silent_output): MigrateDatabase
	{
		$this->silent_output = $silent_output;
		return $this;
	}

	/**
	 * entry point of migration executable
	 *
	 * @param int|null $targetVersion
	 *
	 * @return  $this
	 * @throws DatabaseException
	 * @throws \Exception
	 */
	public function execute(int $targetVersion = null): MigrateDatabase
	{
		if (!$this->hasMigrationTable())
		{
			$this->createMigrationTable();
		}
		$currentVersion = $this->getMigrationVersion();
		list($highest, $migrations) = $this->getAvailableMigrations();

		// means, always migrate to the highest number if nothing else has been submitted
		if (!isset($targetVersion))
		{
			$targetVersion = $highest;
		}

		if (!isset($targetVersion) || $targetVersion > $highest)
		{
			$targetVersion = $currentVersion;
		}

		if ($currentVersion < $targetVersion)
		{
			$direction = 'up to ' . $targetVersion;
		}
		elseif ($currentVersion > $targetVersion)
		{
			$direction = 'down to ' . $targetVersion;
		}
		else
		{
			$direction = 'none';
		}

		$this->stdOutHeader($currentVersion, $targetVersion, $direction);

		if ($currentVersion < $targetVersion)
		{
			for ($number = $currentVersion + 1; $number <= $targetVersion; $number++)
			{
				$this->migrate($number, 'up', $migrations[$number]);
			}
		}
		elseif ($currentVersion > $targetVersion)
		{
			for ($number = $currentVersion; $number >= $targetVersion + 1; $number--)
			{
				$this->migrate($number, 'down', $migrations[$number]);
			}
		}
		else
		{
			$this->stdOut(PHP_EOL . '... Nothing to do ...' . PHP_EOL);
		}

		$this->stdOutFooter();
		return $this;
	}

	/**
	 * checks if migration table is present
	 *
	 * @return bool
	 */
	protected function hasMigrationTable(): bool
	{
		$tmp = $this->getDbh()->getConnectionData();

		$result = $this->getDbh()->show('TABLES', self::MIGRATION_TABLE_NAME );

		return !empty($result);
	}

	/**
	 * adds the migration table to database
	 *
	 * @return $this
	 */
	protected function createMigrationTable()
	{
		$sql = "CREATE TABLE IF NOT EXISTS `" . self::MIGRATION_TABLE_NAME . "` ( `version` INTEGER NOT NULL PRIMARY KEY)";
		$this->getDbh()->executeQuery($sql);

		$sql = "INSERT INTO `" . self::MIGRATION_TABLE_NAME . "` (`" . $this->fieldName . "`) VALUES (0)";
		$this->getDbh()->insert($sql);

		return $this;
	}

	/**
	 * gets the current migration version from database
	 *
	 * @return int
	 */
	protected function getMigrationVersion(): int
	{
		$sql = $this->QueryBuilder->buildSelectQuery($this->fieldName, self::MIGRATION_TABLE_NAME);
		$result = $this->getDbh()->getSingleValue($sql);
		$this->version = (int) $result;
		return $this->version;
	}

	/**
	 * updates the migration version on database
	 *
	 * @param int $version
	 *
	 * @return  $this
	 */
	protected function setMigrationVersion(int $version): MigrateDatabase
	{
		$sql = $this->QueryBuilder->buildUpdateQuery(
			self::MIGRATION_TABLE_NAME, array($this->fieldName => $version), $this->fieldName .' > 0'
		);
		$this->getDbh()->update($sql);
		return $this;
	}

	/**
	 * scans the migration directory for all available migration files
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function getAvailableMigrations(): array
	{
		$migrationScripts = scandir($this->migrationFilePath);
		$matches = array();
		$migrations = array();
		$highest = 0;

		foreach ($migrationScripts as $migrationScript)
		{
			if (is_dir($this->migrationFilePath . DIRECTORY_SEPARATOR . $migrationScript) === true)
			{
				continue;
			}
			elseif (preg_match('/^(\d+)_(.*?)(?:\.(up|down)){0,1}(?:\.(sql|php))$/', $migrationScript, $matches))
			{
				$number    = (int) $matches[1];
				$name      = $matches[2];
				$direction = $matches[3];
				$extension = $matches[4];
			}
			else
			{
				throw new \Exception('Wrong migration script name: [' . $migrationScript . ']');
			}

			if (isset($migrations[$number][$direction]))
			{
				throw new \Exception('Migration [' . $number . ' => ' . $direction . '] doubled!');
			}

			if ($extension == 'php')
			{
				$migrations[$number]['up'] = $this->getFileName($number, $direction, $name);
				$migrations[$number]['down'] = $this->getFileName($number, $direction, $name);
			}
			else
			{
				$migrations[$number][$direction] = $this->getFileName($number, $direction, $name);
			}

			if ($highest < $number)
			{
				$highest = $number;
			}
		}

		return array(
			$highest,
			$migrations
		);
	}

	/**
	 * Return "Fully-Qualified" migration file name with path:
	 * 	- SQL file name
	 * 	- PHP file name
	 *
	 * @param integer $number    migration number
	 * @param string  $direction "up"|"down"
	 * @param string  $name      migration name
	 *
	 * @return	string	(realpath of file name)
	 *
	 * @throws	\Exception
	 */
	protected function getFileName(int $number, string $direction, string $name): string
	{
		$pathWithPrefix = $this->migrationFilePath . str_pad($number, 3, '0', STR_PAD_LEFT) . '_';

		$sqlFileName = $pathWithPrefix . $name . '.' . $direction . '.sql';
		$phpFileName = $pathWithPrefix . $name . '.php';

		if (file_exists($sqlFileName))
		{
			$fileName = $sqlFileName;
		}
		else
			if (file_exists($phpFileName))
			{
				$fileName = $phpFileName;
			}
			else
			{
				throw new CoreException('Migration [' . $number . ' => ' . $direction . '] not found!');
			}

		return realpath($fileName);
	}

	/**
	 * @param int     $number
	 * @param string  $direction
	 * @param   array $ar_file_names
	 *
	 * @return  array|MigrateDatabase
	 * @throws \Exception
	 */
	protected function migrate(int $number, string $direction, array $ar_file_names): array|MigrateDatabase
	{
		$message = PHP_EOL . 'Run Migration No. ' . $number . ' (' . $direction . ')';
		$this->stdOut($message);

		try
		{
			if (isset($ar_file_names['php']))
			{
				$fileName = $ar_file_names['php'];
			}
			elseif (isset($ar_file_names[$direction]))
			{
				$fileName = $ar_file_names[$direction];
			}
			else
			{
				throw new \Exception('Wrong filename found: ' . var_export($ar_file_names, true));
			}

			$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

			switch (strtolower($fileExtension))
			{
				case 'sql':
					$results = $this->migrateSql($fileName);
					break;

				case 'php':
					$results = $this->migratePhp($fileName, $direction);
					break;

				default:
					throw new \Exception('unknown file extension for file ' . $fileName);
			}

			if ($direction == 'up')
			{
				$this->setMigrationVersion($number);
			}
			else
			{
				$this->setMigrationVersion($number - 1);
			}
		}
		catch (\Exception $e)
		{
			$this->stdOutError($number, $direction);
			echo PHP_EOL;

			printf('Code:    %s' . PHP_EOL, $e->getCode());
			printf('Message: %s' . PHP_EOL, $e->getMessage());

			echo 'Trace: ' . PHP_EOL . $e->getTraceAsString() . PHP_EOL . PHP_EOL;
			throw $e;
		}

		return $results;
	}

	/**
	 * migrate a SQL file
	 *
	 * @param string $file_name
	 *
	 * @return	$this
	 *
	 * @throws DatabaseException
	 */
	protected function migrateSql(string $file_name): MigrateDatabase
	{
		$sqlContent = file_get_contents($file_name);
		$sqlArray   = preg_split('/;\s*\n/', $sqlContent, null, PREG_SPLIT_NO_EMPTY);
		$sql        = 'none yet';

		$this->getDbh()->beginTransaction();

		try
		{
			foreach ($sqlArray as $sql)
			{
				$sql = trim($sql);
				if (!empty($sql))
				{
					// special case, split DELIMITER statements
					if (preg_match('/DELIMITER(.+)/i', $sql))
					{
						$this->stdOut('Found DELIMITER statement, skipped, migrate manually!' . PHP_EOL);
						continue;
					}

					$this->getDbh()->executeQuery($sql);
				}
				else
				{
					$this->stdOut('SQL statement was empty. Skipping...' . PHP_EOL);
				}
			}
			$this->getDbh()->commitTransaction(true);
		}
		catch (\Exception $e)
		{
			$this->getDbh()->rollbackTransaction();
			$message = $e->getMessage() . ' SQL: ' . $sql;
			$code = $e->getCode();
			throw new DatabaseException($message, $code);
		}

		return $this;
	}

	/**
	 * executes a migration with a PHP file
	 *
	 * @param string $file_name
	 * @param string $direction
	 *
	 * @return  $this
	 * @throws \Exception
	 */
	protected function migratePhp(string $file_name, string $direction): MigrateDatabase
	{
		require_once $file_name;

		$className = $this->getClassFromFileName($file_name);
		$migration = new $className($this->getDbh());

		try
		{
			$this->getDbh()->beginTransaction();
			$migration->$direction();
			$this->getDbh()->commitTransaction();;
		}
		catch (\Exception $e)
		{
			$this->getDbh()->rollbackTransaction();
			throw $e;
		}

		return $this;
	}

	/**
	 * @param string $fileName
	 *
	 * @return mixed
	 */
	protected function getClassFromFileName(string $fileName): mixed
	{
		$className = preg_replace(array(
			'~^(\d+_)~iUms',
			'~(\.php)$~iUms'
		), '', basename($fileName));
		return $className;
	}

	/**
	 * Check migration names.
	 *
	 * @param int      $highest
	 * @param	array $migrations
	 *
	 * @return	$this
	 *
	 * @throws	CoreException
	 */
	protected function checkRestrictionsOnMigrationNames(int $highest, array $migrations): MigrateDatabase
	{
		if (isset($migrations[0]))
		{
			throw new CoreException('Migrations with prefix 0 present!');
		}

		for ($number = 1; $number <= $highest; $number++)
		{
			if (!isset($migrations[$number]))
			{
				throw new CoreException('Migrations with prefix ' . $number . ' not present!');
			}

			if (isset($migrations[$number]['php']))
			{
				if (count($migrations[$number]) != 1)
				{
					throw new CoreException('There should be only one PHP migration with prefix ' . $number . '!');
				}
			}
			else
				if (!isset($migrations[$number]['php']))
				{
					if (!isset($migrations[$number]['up']))
					{
						throw new CoreException('There should be one [up] migration with prefix ' . $number . '!');
					}

					if (!isset($migrations[$number]['down']))
					{
						throw new CoreException('There should be one [down] migration with prefix ' . $number . '!');
					}

					if (count($migrations[$number]) != 2)
					{
						throw new CoreException('There should be two migrations [up/down] with prefix ' . $number . '!');
					}

					if (count($migrations[$number]['up']) != 1)
					{
						throw new CoreException('There should be only one name for a migration with prefix ' . $number . '!');
					}

					if (count($migrations[$number]['down']) != 1)
					{
						throw new CoreException('There should be only one name for a migration with prefix ' . $number . '!');
					}
				}
				else
				{
					throw new CoreException('No php extension for migration with prefix [' . $number . ']!');
				}
		}

		return $this;
	}

	/**
	 * prints the cli header on stdOut
	 *
	 * @param int    $currentVersion
	 * @param int    $targetVersion
	 * @param string $direction
	 *
	 * @return  $this
	 */
	protected function stdOutHeader(int $currentVersion, int $targetVersion, string $direction): MigrateDatabase
	{
		$db = $this->dbh->getConnectionData();
		$text = <<<TXT
----- Database migrations ----
Path:      {$this->migrationFilePath}
DB Host:   {$db['host']}
DB Name:   {$db['db_name']}
Current:   $currentVersion
Target:    $targetVersion
Direction: $direction

----------- Start ------------

TXT;
		return $this->stdOut($text);
	}

	/**
	 * prints the footer of cli
	 *
	 * @return $this
	 */
	protected function stdOutFooter(): MigrateDatabase
	{
		$text = <<<TXT

------------ End -------------
New Current: {$this->getMigrationVersion()}
done

TXT;

		return $this->stdOut($text);
	}

	/**
	 * prints an error message on stdOut
	 *
	 * @param int       $number
	 * @param	string $direction
	 *
	 * @return  $this
	 */
	protected function stdOutError(int $number, string $direction): MigrateDatabase
	{
		$text = <<<TXT

-----------FAILURE !!! --------

Migration no $number ($direction) failed!

Abort!

-----------FAILURE !!! --------

TXT;
		echo $text;
		return $this;
	}

	/**
	 * wrapper for echo, respecting the silent flag
	 *
	 * @param string $text
	 *
	 * @return  $this
	 */
	private function stdOut(string $text): MigrateDatabase
	{
		if (!$this->silent_output)
		{
			echo $text;
		}
		return $this;
	}
}

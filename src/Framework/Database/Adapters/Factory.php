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

namespace App\Framework\Database\Adapters;

use App\Framework\Database\DBHandler;
use App\Framework\Exceptions\DatabaseException;

/**
 * Factory for creating database connections with specified adapters.
 *
 * Currently, the application avoids PDO in favor of using specific adapters
 * (MySqliAdapter and Sqlite3Adapter). This choice could be due to several reasons:
 *
 * Performance Optimization: Direct driver-specific adapters (like mysqli and sqlite3)
 * can offer faster execution than PDO, especially if the application has been tailored
 * to take advantage of specific functionalities offered by these drivers.
 *
 * Control and Flexibility: By using dedicated adapters, we gain more control over the
 * database operations, allowing more customization and error handling specific to each driver.
 *
 * Testing and Stability: The existing structure might have been thoroughly tested and
 * optimized with MySqliAdapter and Sqlite3Adapter. PDO is left as a potential option f
 * or testing or future compatibility, but specific adapters are preferred for production stability.
 */
class Factory
{
	/**
	 * Creates a DBHandler instance with the appropriate database adapter.
	 *
	 * Supports 'MYSQLI' and 'SQLITE3' drivers. Throws an exception if the driver is unsupported.
	 *
	 * @param array $credentials Database connection credentials, including 'db_driver' (e.g., 'MYSQLI', 'SQLITE3').
	 * @return DBHandler Configured database handler.
	 * @throws DatabaseException On connection failure.
	 * @throws \InvalidArgumentException If an unsupported driver is specified.
	 */
	public static function createConnection(array $credentials): DBHandler
	{
		$credentials['db_driver'] = strtoupper($credentials['db_driver']);

		$adapter = match($credentials['db_driver'])
		{
			// PDO is only a test alternative.
			//'PDO_MYSQL', 'PDO_SQLITE' => new PdoAdapter(),
			'MYSQLI'  => new MySqliAdapter(),
			'SQLITE3' => new Sqlite3Adapter(),
			default => throw new \InvalidArgumentException("Unsupported database driver: {$credentials['db_driver']}"),
		};

		$adapter->connect($credentials);

		return new DBHandler($adapter);
	}
}

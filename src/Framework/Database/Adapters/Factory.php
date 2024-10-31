<?php

namespace App\Framework\Database\Adapters;

use App\Framework\Database\DBHandler;
use App\Framework\Exceptions\DatabaseException;

class Factory
{

	/**
	 * @throws DatabaseException
	 */
	public static function createConnection(array $credentials): DBHandler
	{
		$credentials['db_driver'] = strtoupper($credentials['db_driver']);

		$adapter = match($credentials['db_driver'])
		{
			//'PDO_MYSQL', 'PDO_SQLITE' => new PdoAdapter(),
			'MYSQLI'  => new MySqliAdapter(),
			'SQLITE3' => new Sqlite3Adapter(),
			default => throw new \InvalidArgumentException("Unsupported database driver: {$credentials['db_driver']}"),
		};

		$adapter->connect($credentials);

		return new DBHandler($adapter);
	}

}
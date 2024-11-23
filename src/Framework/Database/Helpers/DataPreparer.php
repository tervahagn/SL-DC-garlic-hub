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

namespace App\Framework\Database\Helpers;

use App\Framework\Database\DBHandler;

/**
 * Abstract class DataPreparer
 *
 * Defines common data preparation methods for database operations.
 */
abstract class DataPreparer
{
	const FIELD_TYPE_STRING     = 'string';
	const FIELD_TYPE_INTEGER    = 'integer';
	const FIELD_TYPE_SET        = 'set';
	const FIELD_TYPE_FLOAT      = 'float';
	const FIELD_TYPE_SERIALIZED = 'serialize';
	const FIELD_TYPE_IP         = 'ip';
	const FIELD_TYPE_DATETIME   = 'datetime';

	/**
	 * @var DBHandler Database adapter
	 */
	protected DBHandler $dbh;

	/**
	 * @param DBHandler $dbh
	 */
	public function __construct(DBHandler $dbh)
	{
		$this->dbh = $dbh;
	}

	/**
	 * Abstract method to prepare fields for DB
	 *
	 * Must be implemented in subclasses with specific field preparation logic.
	 *
	 * @param array $fields Fields to prepare
	 * @return array Prepared fields
	 */
	abstract public function prepareForDB(array $fields): array;

	/**
	 * @param $value
	 * @return string
	 */
	public function quoteString($value): string
	{
		return "'" . $this->dbh->escapeString($value) . "'";
	}


	/**
	 * @param mixed  $index
	 * @param array  $fields
	 * @param string $type
	 *
	 * @return array
	 */
	protected function quoteField(mixed $index, array $fields, string $type): array
	{
		switch($type)
		{
			case self::FIELD_TYPE_STRING:
				if (array_key_exists($index, $fields)) $fields[$index] = $this->quoteString($fields[$index]);
				break;

			case self::FIELD_TYPE_SET:
				if (array_key_exists($index, $fields))
				{
					$fields[$index] = $this->quoteString(implode(',', array_unique($fields[$index])));
				}
				break;

			case self::FIELD_TYPE_INTEGER:
				if (array_key_exists($index, $fields)) $fields[$index] = (int)$fields[$index];
				break;

			case self::FIELD_TYPE_FLOAT:
				if (array_key_exists($index, $fields)) $fields[$index] = floatval($fields[$index]);
				break;

			case self::FIELD_TYPE_SERIALIZED:
				if (array_key_exists($index, $fields)) $fields[$index] = "'" . serialize($fields[$index]) . "'";
				break;

			case self::FIELD_TYPE_IP:
				if (array_key_exists($index, $fields)) $fields[$index] = 'INET_ATON('."'".$fields[$index]."')";
				break;

			case self::FIELD_TYPE_DATETIME:
				if (array_key_exists($index, $fields) && !$this->containsDateFunction($fields[$index]))
				{
					$fields[$index] = "'" . $this->quoteString($fields[$index]) . "'";
				}
				break;
		}

		return $fields;
	}

	/**
	 * Determines if a value is a DATE function.
	 *
	 * @param string $value Value to check
	 * @return bool True if value is a MySQL DATE function, false otherwise
	 */
	protected function containsDateFunction(string $value): bool
	{
		// all known data fields in MySQL, PostgreSQL und SQLite
		$dateFunctions = [
			'NOW()', 'DATE_FORMAT', 'TIMESTAMP', 'DATE_SUB', 'DATE_ADD', // MySQL
			'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'EXTRACT', 'AGE', // PostgreSQL
			 'DATE', 'TIME', 'DATETIME', 'JULIANDAY' // SQLite
		];

		foreach ($dateFunctions as $function)
		{
			if (str_contains(strtoupper($value), $function))
				return true;
		}

		return false;
	}

}

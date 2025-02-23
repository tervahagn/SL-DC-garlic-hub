<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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

namespace App\Framework\Core;

class Validator
{
	public function isInt(string $value): bool
	{
		return filter_var($value, FILTER_VALIDATE_INT) !== false;
	}

	public function isFloat(string $value): bool
	{
		return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
	}

	public function isBool(string $value): bool
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
	}

	public function isEmail(string $value): bool
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}

	public function isString(string $value): bool
	{
		return is_string($value);
	}

	public function isHtml(string $value): bool
	{
		return is_string($value);
	}

	public function isJson(string $value): bool
	{
		json_decode($value); // Just decode, don't need the result for validation
		return json_last_error() === JSON_ERROR_NONE;
	}

	public function isStringArray(array $values): bool
	{
		if (!is_array($values))
			return false;

		foreach($values as $value)
		{
			if (!is_string($value))
				return false;

		}
		return true;
	}

	public function isIntArray(array $values): bool
	{
		if (!is_array($values))
			return false;

		foreach($values as $value)
		{
			if (!is_numeric($value))
				return false;
		}
		return true;
	}

	public function isFloatArray(array $values): bool
	{
		if (!is_array($values))
			return false;

		foreach($values as $value)
		{
			if (!is_numeric($value))
				return false;
		}
		return true;
	}

}

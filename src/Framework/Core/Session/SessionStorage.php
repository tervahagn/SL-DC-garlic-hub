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

namespace App\Framework\Core\Session;


class SessionStorage
{
	public function get(string $key): null|string|array
	{
		return $this->exists($key) ? $_SESSION[$key] : null;
	}

	public function set(string $key, string|array $value): void
	{
		$_SESSION[$key] = $value;
	}

	public function delete($key): void
	{
		if ($this->exists($key))
			unset($_SESSION[$key]);
	}

	public function clear(): void
	{
		$_SESSION = [];
	}

	public function exists(string $key): bool
	{
		return array_key_exists($key, $_SESSION);
	}

	public static function id(bool $new = false): false|string
	{
		if ($new && session_id())
			session_regenerate_id(true);

		return session_id() ?: '';
	}

}
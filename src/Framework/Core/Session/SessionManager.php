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

use App\Framework\Exceptions\FrameworkException;

class SessionManager
{
	const string SESSION_NAME = 'GhSid';

	public function __construct()
	{
		session_name(self::SESSION_NAME);
	}

	public function start(array $options = []): void
	{
		$defaultOptions = [
			'cookie_lifetime' => 0, // to end when browser closed
			'cookie_path' => '/',
			'cookie_domain' => '',
			'cookie_secure' => true,
			'cookie_httponly' => true,
			'cookie_samesite' => 'Lax',
		];

		$settings = array_merge($defaultOptions, $options);

		session_start($settings);
	}

	public function getSessionConfig(): array
	{
		return [
			'name' => session_name(),
			'lifetime' => ini_get('session.cookie_lifetime'),
			'path' => ini_get('session.cookie_path'),
			'domain' => ini_get('session.cookie_domain'),
			'secure' => ini_get('session.cookie_secure'),
			'httponly' => ini_get('session.cookie_httponly'),
			'samesite' => ini_get('session.cookie_samesite'),
		];
	}

	public function isActive(): bool
	{
		return session_status() === PHP_SESSION_ACTIVE;
	}

	/**
	 * @throws FrameworkException
	 */
	public function regenerateID(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE)
			throw new FrameworkException('Session not active for regenerating.');

		session_regenerate_id(true);
		setcookie(session_name(), session_id(), ini_get("session.cookie_lifetime"), "/");
	}

	public function destroy(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE)
		{
			session_unset();
			session_destroy();
		}
	}

}
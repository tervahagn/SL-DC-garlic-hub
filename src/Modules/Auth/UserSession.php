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


namespace App\Modules\Auth;

use App\Framework\Core\Session;
use App\Framework\Exceptions\UserException;

class UserSession
{
	private readonly Session $session;

	/** @var array{UID:int, username:string, locale:string}|null */
	private ?array $user = null;
	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	/**
	 * @throws UserException
	 */
	public function getUID(): int
	{
		$this->checkUser();
		if ($this->user === null)
			throw new UserException("Attempted to get UID for unauthenticated user.");

		return $this->user['UID'];
	}

	/**
	 * @throws UserException
	 */
	public function getUsername(): string
	{
		$this->checkUser();

		if ($this->user === null)
			throw new UserException("Attempted to get UID for unauthenticated user.");

		return $this->user['username'];
	}

	/**
	 * @throws UserException
	 */
	public function getLocales(): string
	{
		$this->checkUser();

		if ($this->user === null)
			throw new UserException("Attempted to get UID for unauthenticated user.");

		return (string) $this->user['locale'];
	}

	/**
	 * @throws UserException
	 */
	private function checkUser(): void
	{
		$sessionData = $this->session->get('user');
		if (!is_array($sessionData) || !isset($sessionData['UID'], $sessionData['username'], $sessionData['locale']))
			throw new UserException('User not found in session.');

		/** @var array{UID:int, username:string, locale:string} $sessionData */
		$this->user = $sessionData;
	}


}
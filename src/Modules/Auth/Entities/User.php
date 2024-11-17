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

namespace App\Modules\Auth\Entities;

/**
 * Basic user model with roles and password for authentication.
 */
class User
{
	private int|string $UID;
	private array $userData;
	private array $roles;

	/**
	 * @param array $userData
	 * @param array $roles
	 */
	public function __construct(array $userData, array $roles = ['ROLE_USER'])
	{
		$this->UID      = $userData['UID'];
		$this->userData = $userData;
		$this->roles = $roles;
	}

	/**
	 * Gets the hashed password.
	 *
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->userData['password'];
	}

	public function getUsername(): string
	{
		return $this->userData['username'];
	}

	public function getUID(): int|string
	{
		return $this->UID;
	}

	public function getRoles(): array
	{
		return $this->roles;
	}

	public function getLocale()
	{
		return $this->userData['locale'];
	}

	public function getCompanyId()
	{
		return $this->userData['company_id'];
	}

	public function getStatus()
	{
		return $this->userData['status'];
	}
}

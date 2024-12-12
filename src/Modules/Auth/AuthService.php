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

namespace App\Modules\Auth;

use App\Framework\Exceptions\UserException;
use App\Framework\User\UserEntity;
use App\Framework\User\UserService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Cache\InvalidArgumentException;

class AuthService
{
	private UserService $userService;

	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}

	/**
	 * Verifiziert die Anmeldedaten eines Benutzers.
	 *
	 * @param string $identifier
	 * @param string $password
	 * @return UserEntity
	 * @throws UserException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function login(string $identifier, string $password): UserEntity
	{
		$user_data = $this->userService->findUser($identifier);

		if (empty($user_data))
			throw new UserException('Invalid credentials.');

		if (!password_verify($password, $user_data['password']))
			throw new UserException('Invalid credentials.');

		$this->userService->invalidateCache($user_data['UID']);

		return $this->userService->getCurrentUser($user_data['UID']);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function logout(array $user)
	{
		$this->userService->invalidateCache($user['UID']);
	}
}
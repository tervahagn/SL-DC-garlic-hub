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

namespace App\Modules\Auth\Repositories;

use App\Framework\BaseRepositories\Sql;
use App\Framework\Database\DBHandler;
use App\Framework\Database\Helpers\DataPreparer;
use App\Framework\Database\QueryBuilder;
use App\Modules\Auth\Entities\User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provides user data handling for authentication.
 */
class UserMain extends Sql implements UserProviderInterface
{
	/**
	 * @param DBHandler $dbh Database handler
	 * @param QueryBuilder $queryBuilder Query builder
	 * @param UserMainDataPreparer $dataPreparer Data preparer
	 * @param string $table Database table name
	 * @param string $id_field ID field name
	 */
	public function __construct(DBHandler $dbh, QueryBuilder $queryBuilder, DataPreparer $dataPreparer, string $table, string $id_field)
	{
		parent::__construct($dbh, $queryBuilder, $dataPreparer, 'user_main', 'UID');
	}

	/**
	 * Reloads user data by identifier.
	 *
	 * @param UserInterface $user The user to refresh
	 * @return UserInterface
	 */
	public function refreshUser(UserInterface $user): UserInterface
	{
		return $this->loadUserByIdentifier($user->getUserIdentifier());
	}

	/**
	 * Checks if this provider supports a given user class.
	 *
	 * @param string $class Class name to check
	 * @return bool
	 */
	public function supportsClass(string $class): bool
	{
		return $class === User::class;
	}

	/**
	 * Loads a user by their identifier.
	 *
	 * @param string $identifier Username identifier
	 * @return UserInterface
	 * @throws UserNotFoundException If user is not found
	 */
	public function loadUserByIdentifier(string $identifier): UserInterface
	{
		if (filter_var($identifier, FILTER_VALIDATE_EMAIL))
		{
			$where = "email = '$identifier'";
		}
		else
		{
			$where = "username = '$identifier'";
		}

		$result = $this->getFirstDataSet($this->findAllBy($where));

		if (empty($result))
		{
			$exception = new UserNotFoundException();
			$exception->setUserIdentifier($identifier);
			throw $exception;
		}

		return new User($result['username'], $result['password']); // Adjust to match User class
	}
}

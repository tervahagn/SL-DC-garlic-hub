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
use App\Framework\Exceptions\UserException;
use App\Modules\Auth\Entities\User;
use Doctrine\DBAL\Connection;

/**
 * Provides user data handling for authentication.
 */
class UserMain extends Sql
{
	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'user_main', 'UID');
	}

	/**
	 * @param string $identifier
	 *
	 * @return User
	 * @throws UserException
	 */
	public function loadUserByIdentifier(string $identifier): User
	{
		if (filter_var($identifier, FILTER_VALIDATE_EMAIL))
			$conditions = ['email' => $identifier];
		else
			$conditions = ['username' => $identifier];

		$result = $this->getFirstDataSet($this->findAllBy($conditions));
		if (empty($result))
			throw new UserException('User not found.');

		return new User($result);
	}
}

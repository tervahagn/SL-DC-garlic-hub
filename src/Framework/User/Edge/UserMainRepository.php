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

namespace App\Framework\User\Edge;

use App\Framework\BaseRepositories\Sql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * Provides user data handling for authentication.
 */
class UserMainRepository extends Sql
{
	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'user_main', 'UID');
	}

	/**
	 * We do not want to use * as this will transfer user sensitive data
	 * like passwords, tokens etc.
	 *
	 * @throws Exception
	 */
	public function findById(int|string $id): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('UID, company_id, status, locale')
			->from($this->table)
			->where($this->idField . ' = :id')
			->setParameter('id', $id);

		return $queryBuilder->executeQuery()->fetchAssociative();
	}

	/**
	 * @param string $identifier
	 *
	 * @return array
	 * @throws Exception
	 */
	public function findByIdentifier(string $identifier): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('UID, password, locale, status, company_id')->from($this->table);

		if (filter_var($identifier, FILTER_VALIDATE_EMAIL))
			$queryBuilder->where('email = :identifier');
		else
			$queryBuilder->where('username = :identifier');

		$queryBuilder->setParameter('identifier', $identifier);

		return $queryBuilder->executeQuery()->fetchAssociative();
	}
}

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


namespace App\Modules\Users\Repositories\Edge;

use App\Framework\Database\BaseRepositories\SqlBase;
use App\Framework\Database\BaseRepositories\Traits\CrudTraits;
use App\Framework\Database\BaseRepositories\Traits\FindOperationsTrait;
use App\Framework\Exceptions\DatabaseException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class UserTokensRepository extends SqlBase
{
	use CrudTraits, FindOperationsTrait;

	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'user_tokens', 'token');
	}

	/**
	 * @return array<string,mixed>
	 * @throws Exception|DatabaseException
	 */
	public function findFirstByToken(string $token): array
	{
		$token = hex2bin($token);
		if ($token === false)
			throw new DatabaseException('Invalid token');

		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('user_tokens.*, username, status')
			->from($this->table)
			->leftJoin('user_tokens', 'user_main', '', 'user_main.UID = user_tokens.UID')
			->where('token = :token')
			->setParameter('token', $token);

		return $this->fetchAssociative($queryBuilder);
	}

	/**
	 * @return list<array<string,mixed>>
	 * @throws Exception
	 */
	public function findValidByUID(int $UID): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('token, UID, purpose, expires_at')
			->from($this->table)
			->where('UID = :uid')
			->andWhere('used_at IS NULL')
			->setParameter('uid', $UID);

		return $queryBuilder->executeQuery()->fetchAllAssociative();
	}

}
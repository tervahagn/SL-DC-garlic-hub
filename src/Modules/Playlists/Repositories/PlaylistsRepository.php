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

namespace App\Modules\Playlists\Repositories;

use App\Framework\Database\BaseRepositories\FilterBase;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;

class PlaylistsRepository extends FilterBase
{
	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'playlists', 'playlist_id');
	}

	public function delete(int|string $id): int
	{
		$platform   = $this->connection->getDatabasePlatform();
		$driverName = strtolower(str_replace('Doctrine\DBAL\Platforms\\', '', get_class($platform)));
		if ($driverName === 'sqliteplatform')
			$this->connection->executeQuery('PRAGMA foreign_keys = ON');

		return parent::delete($id);
	}

	/**
	 * @return array<string, mixed>
	 * @throws Exception
	 */
	public function findFirstWithUserName(int $playlistId): array
	{
		$select = $this->prepareSelectFilteredForUser();
		$join   = ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
		$where  = ['playlist_id' => ['value' => $playlistId, 'operator' => '=']];
		return $this->getFirstDataSet($this->findAllByWithFields($select, $where, $join));
	}

	/**
	 * @param array<string, mixed> $saveData
	 * @throws Exception
	 */
	public function updateExport(int $playlistId, array $saveData): int|string
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->update($this->table);
		foreach ($saveData as $key => $value)
		{
			$queryBuilder->set($key,$value);
		}
		$queryBuilder->set('export_time', 'CURRENT_TIMESTAMP');

		$queryBuilder->where('playlist_id = :playlist_id');
		$queryBuilder->setParameter('playlist_id', $playlistId, ParameterType::INTEGER);

		return $queryBuilder->executeStatement();
	}

	/**
	 * @return string[]
	 */
	protected function prepareJoin(): array
	{
		return ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
	}

	/**
	 * @return string[]
	 */
	protected function prepareSelectFiltered(): array
	{
		return [$this->table.'.*'];
	}

	/**
	 * @return string[]
	 */
	protected function prepareSelectFilteredForUser(): array
	{
		return array_merge($this->prepareSelectFiltered(),['user_main.username', 'user_main.company_id']);
	}

	/**
	 * @param array<string, mixed> $filterFields
	 * @return array<string, mixed>
	 */
	protected function prepareWhereForFiltering(array $filterFields): array
	{
		$where = [];
		foreach ($filterFields as $key => $parameter)
		{
			switch ($key)
			{
				case 'playlist_mode':
					if (empty($parameter['value']))
						break;
						$position = strpos($parameter['value'], ',');
					if ($position === false)
						$where[$key] = $this->generateWhereClause($parameter['value']);
					else
						$where[$key] = $this->generateWhereClause($parameter['value'], 'IN', 'AND,', ArrayParameterType::STRING);
					break;

				default:
					$clause = $this->determineWhereForFiltering($key, $parameter);
					if (!empty($clause))
					{
						$where = array_merge($where, $clause);
					}
			}
		}
		return $where;
	}

}
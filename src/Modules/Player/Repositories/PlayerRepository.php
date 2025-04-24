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


namespace App\Modules\Player\Repositories;

use App\Framework\Database\BaseRepositories\FilterBase;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\PlayerActivity;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class PlayerRepository extends FilterBase
{

	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'player', 'player_id');
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	public function findPlayerByUuid(string $uuid): array
	{
		// skip overhead
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('player_id, status, licence_id, '.$this->table.'.UID, uuid, '.$this->table.'.name,  commands,
					location_data, location_longitude, '.$this->table.'.playlist_id, UNIX_TIMESTAMP('.$this->table.'.last_update) as updated_player, properties, playlist_mode, playlist_name, multizone, UNIX_TIMESTAMP(playlists.last_update) as updated_playlist, remote_administration, screen_times');
		$queryBuilder->from($this->table);
		$queryBuilder->leftJoin($this->table, 'playlists', '', 'playlists.playlist_id =' . $this->table . '.playlist_id');
		$queryBuilder->where('uuid = :uuid');
		$queryBuilder->setParameter('uuid', $uuid);

		$result = $queryBuilder->executeQuery()->fetchOne();
		if ($result === false or empty($result))
			throw new ModuleException('playlist_index', 'Playlist not found');

		return $result;
	}
	protected function prepareJoin(): array
	{
		return [
			'user_main' => 'user_main.UID=' . $this->table . '.UID',
			'playlists' => 'playlists.playlist_id =' . $this->table . '.playlist_id'
		];
	}

	protected function prepareSelectFiltered(): array
	{
		return [$this->table.'.*, playlists.playlist_name'];
	}

	protected function prepareSelectFilteredForUser(): array
	{
		return array_merge($this->prepareSelectFiltered(),['user_main.username', 'user_main.company_id']);
	}

	protected function prepareWhereForFiltering(array $filterFields): array
	{
		$where = [];
		foreach ($filterFields as $key => $parameter)
		{
			switch ($key)
			{
				case 'activity':
					if (empty($parameter['value']))
						break;

				if ($parameter['value'] === PlayerActivity::ACTIVE->value)
					$where[] = '(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_access)) < refresh * 2';
				else
					$where[] = '(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_access)) > refresh * 2';
					break;

				case 'playlist_id':
					if ($parameter['value'] > 0)
					{
						$where[] = 'playlist_id = '. $parameter['value'];
					}
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
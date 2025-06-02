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
	 * @throws Exception
	 */
	public function findPlaylistIdsByPlaylistIds(array $playlistIds): array
	{
		if (empty($playlistIds))
			return [];

		$ids =  implode(',', $playlistIds);
		$sql = 'SELECT playlist_id FROM '.$this->getTable().' WHERE playlist_id IN('.$ids.')';

		return $this->connection->executeQuery($sql)->fetchAllAssociative();
	}

	protected function prepareJoin(): array
	{
		return [
			'user_main' => 'user_main.UID = ' . $this->table . '.UID',
			'playlists' => 'playlists.playlist_id = ' . $this->table . '.playlist_id'
		];
	}


	protected function prepareSelectFiltered(): array
	{
		return ['player_id',
				$this->table.'.playlist_id',
				'playlist_name',
				'firmware', $this->table.'.status', 'model',
				'commands',	'reports',
				$this->table.'.last_access',
				'refresh', 'player_name, '.$this->table.'.UID'
		];
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
					$where[$key] = '(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_access)) < refresh * 2';
				else
					$where[$key] = '(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_access)) > refresh * 2';
					break;

				case 'playlist_id':
					if ($parameter['value'] > 0)
					{
						$where[$key] = $parameter['value'];
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
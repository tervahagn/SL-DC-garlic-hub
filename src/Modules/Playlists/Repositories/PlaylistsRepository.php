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
use App\Modules\Playlists\Helper\ItemType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class PlaylistsRepository extends FilterBase
{

	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'playlists', 'playlist_id');
	}

	/**
	 * @throws Exception
	 */
	public function findFirstWithUserName(int $playlistId): array
	{
		$select = $this->prepareSelectFilteredForUser();
		$join   = ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
		$where  = ['playlist_id' => ['value' => $playlistId, 'operator' => '=']];
		$result =  $this->getFirstDataSet($this->findAllByWithFields($select, $where, $join));
		return $result;
	}

	public function findAllByItemsAsPlaylistAndMediaId(mixed $fileResource): array
	{
		$itemsTable = 'playlists_items';

		$fields = [$this->table.'.*'];
		$join   = [$itemsTable => $itemsTable.'.playlist_id = '.$this->table.'.playlist_id'];
		$where  = [
			$itemsTable . '.file_resource' => $this->generateWhereClause($fileResource),
			$itemsTable . '.item_type' => $this->generateWhereClause(ItemType::PLAYLIST->value)
		];

		return $this->findAllByWithFields($fields, $where, $join);
	}


	protected function prepareJoin(): array
	{
		return ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
	}

	protected function prepareSelectFiltered(): array
	{
		return [$this->table.'.*'];
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
				case 'playlist_mode':
					if (!empty($parameter['value']))
					{
						$where[$key] = ['value' => $parameter['value'], 'operator' => '='];
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
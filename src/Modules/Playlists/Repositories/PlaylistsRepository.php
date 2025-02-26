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
		$select = [$this->table.'.*', 'user_main.username', 'user_main.company_id'];
		$join   = ['user_main' => 'user_main.UID=' . $this->table . '.UID'];
		$where  = ['playlist_id' => ['value' => $playlistId, 'operator' => '=']];

		return $this->getFirstDataSet($this->findAllByWithFields($select, $where, $join));
	}

	protected function prepareJoin()
	{
		// TODO: Implement prepareJoin() method.
	}

	protected function prepareSelectFiltered()
	{
		// TODO: Implement prepareSelectFiltered() method.
	}

	protected function prepareSelectFilteredForUser()
	{
		// TODO: Implement prepareSelectFilteredForUser() method.
	}
}
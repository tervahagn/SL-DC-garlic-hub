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

use App\Framework\Database\BaseRepositories\FindOperationsTrait;
use App\Framework\Database\BaseRepositories\Sql;
use App\Framework\Database\BaseRepositories\TransactionsTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ItemsRepository extends Sql
{
	CONST int FLAG_DISABLED = 1;
	CONST  int FLAG_LOCKED   = 2;
	CONST  int FLAG_LOGGABLE  = 4;

	use TransactionsTrait;
	use FindOperationsTrait;

	public function __construct(Connection $connection)
	{
		parent::__construct($connection, 'playlists_items', 'item_id');
	}

	/**
	 * @throws Exception
	 */
	public function findAllByPlaylistId(int $playlistId): array
	{
		$where   = ['playlist_id' => $this->generateWhereClause($playlistId)];
		$orderBy = [['sort' => 'item_order', 'order' => 'ASC']];

		return $this->findAllBy($where,[], [], '', $orderBy);
	}

	/**
	 * @throws Exception
	 */
	public function sumDurationOfEnabledByPlaylistId(int $playlist_id): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('SUM(item_duration)')
			->from($this->table)
			->where('playlist_id = '.$playlist_id)
		/*	->andWhere('flags & '.self::FLAG_DISABLED.' <> 0')*/
			->groupBy('playlist_id');

		$result = $queryBuilder->fetchOne();
		if ($result === false)
			return  0;

		return (int) $result;
	}

	/**
	 * @throws Exception
	 */
	public function sumDurationOfItemsByUIDAndPlaylistId(int $UID, int $playlist_id): int
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$queryBuilder->select('SUM(item_filesize) as total')
			->from($this->table)
			->where('playlist_id = '.$playlist_id)
			->andWhere('UID = '.$UID)
			->andWhere('flags & 1 <> 0')
			->groupBy('playlist_id');

		$result = $queryBuilder->fetchOne();
		if (!isset($result['total']))
		{
			return  0;
		}
		return $result['total'];
	}


	/**
	 * @throws Exception
	 */
	public function sumAndCountByPlaylistId(int $playlist_id): array
	{
		$select = ['SUM(item_filesize) as totalSize', 'COUNT(item_id) as totalEntries'];
		$where['playlist_id']  = $this->generateWhereClause($playlist_id);

		return $this->getFirstDataSet($this->findAllByWithFields($select, $where));
	}


	/**
	 * @throws Exception
	 */
	public function updateItemOrder($itemId, $newOrder): int
	{
		return  $this->update($itemId,['item_order' => $newOrder]);
	}


}
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
use App\Framework\Database\BaseRepositories\Sql;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\PlayerActivity;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * To save overhead of FilterBase
 */
class PlayerIndexRepository extends Sql
{

	public function __construct(Connection $connection)
	{
		parent::__construct($connection,'player', 'player_id');
	}

	public function findPlayerById(string $Id): array
	{
		$queryBuilder = $this->connection->createQueryBuilder();
		$this->buildQueryForIndex($queryBuilder);
		$queryBuilder->where('player_id = :id');
		$queryBuilder->setParameter('id', $Id);
		$result = $queryBuilder->executeQuery()->fetchAssociative();
		if (empty($result))
			return [];

		return $this->expandResult($result);
	}

	/**
	 * @throws Exception
	 */
	public function insertPlayer(array $saveData): int
	{
		$saveData = $this->implodeSaveData($saveData);

		return $this->insert($saveData);
	}

	/**
	 * @throws Exception
	 */
	public function findPlayerByUuid(string $uuid): array
	{
		// skip overhead
		$queryBuilder = $this->connection->createQueryBuilder();
		$this->buildQueryForIndex($queryBuilder);
		$queryBuilder->where('uuid = :uuid');
		$queryBuilder->setParameter('uuid', $uuid);

		$result = $queryBuilder->executeQuery()->fetchAssociative();
		if (empty($result))
			return [];

		return $this->expandResult($result);
	}

	private function buildQueryForIndex(QueryBuilder $queryBuilder): void
	{
		$queryBuilder->select('player_id, status, licence_id, '.$this->table.'.UID, uuid, '.$this->table.'.player_name,  commands, reports, location_data, location_longitude, location_latitude, '.$this->table.'.playlist_id, '.$this->table.'.last_update as updated_player, properties, playlist_mode, playlist_name, multizone,playlists.last_update as last_update_playlist, remote_administration, screen_times');
		$queryBuilder->from($this->table);
		$queryBuilder->leftJoin($this->table, 'playlists', '', 'playlists.playlist_id =' . $this->table . '.playlist_id');
	}

	private function expandResult(array $result): array
	{
		$result['commands']              = $this->secureExplode($result['commands']);
		$result['reports']               = $this->secureExplode($result['reports']);
		$result['location_data']         = $this->secureUnserialize($result['location_data']);
		$result['properties']            = $this->secureUnserialize($result['properties']);
		$result['remote_administration'] = $this->secureUnserialize($result['remote_administration']);
		$result['categories']            = $this->secureExplode($result['categories']);
		$result['screen_times']          = $this->secureUnserialize($result['screen_times']);

		return $result;
	}

	private function implodeSaveData(array $result): array
	{
		$result['commands']              = $this->secureImplode($result['commands']);
		$result['reports']               = $this->secureImplode($result['reports']);
		$result['location_data']         = $this->secureSerialize($result['location_data']);
		$result['properties']            = $this->secureSerialize($result['properties']);
		$result['remote_administration'] = $this->secureSerialize($result['remote_administration']);
		$result['categories']            = $this->secureSerialize($result['categories']);
		$result['screen_times']          = $this->secureSerialize($result['screen_times']);

		return $result;
	}
}
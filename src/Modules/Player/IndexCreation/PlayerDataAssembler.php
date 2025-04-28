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


namespace App\Modules\Player\IndexCreation;

use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\Entities\PlayerEntityFactory;
use App\Modules\Player\Enums\PlayerModel;
use App\Modules\Player\Enums\PlayerStatus;
use App\Modules\Player\Repositories\PlayerIndexRepository;
use Doctrine\DBAL\Exception;

class PlayerDataAssembler
{
	private UserAgentHandler $userAgentHandler;
	private readonly PlayerIndexRepository $playerRepository;
	private readonly PlayerEntityFactory $playerEntityFactory;

	public function __construct(UserAgentHandler $userAgentHandler,
		PlayerIndexRepository $playerRepository,
		PlayerEntityFactory $playerEntityFactory)
	{
		$this->userAgentHandler = $userAgentHandler;
		$this->playerRepository = $playerRepository;
		$this->playerEntityFactory = $playerEntityFactory;
	}

	public function parseUserAgent(string $userAgent): bool
	{
		$this->userAgentHandler->parseUserAgent($userAgent);
		if ($this->userAgentHandler->getModel() === PlayerModel::UNKNOWN)
			return false;

		return true;
	}

	/**
	 * @throws Exception
	 * @throws ModuleException
	 */
	public function handleLocalPlayer(): PlayerEntity
	{
		$result = $this->playerRepository->findPlayerById(1);

		if (empty($result))
		{
			$saveData = $this->buildInsertArray();
			// we need this to init playerEntity not with normal default values.
			$result   = ['player_id'  => 1, 'status' => PlayerStatus::RELEASED->value, 'licence_id' => 1];

			$id = $this->playerRepository->insertPlayer(array_merge($saveData, $result));
			if ($id === 0)
				throw new ModuleException('player_index', 'Failed to insert local player');


		}
		else if ($result['uuid'] !== $this->userAgentHandler->getUuid())
			throw new ModuleException('player_index', 'Wrong Uuid for local player: '. $result['uuid'] .' != Agent'. $this->userAgentHandler->getUuid());

		return $this->playerEntityFactory->create($result, $this->userAgentHandler);
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	public function insertNewPlayer(int $ownerId): PlayerEntity
	{
		$saveData = $this->buildInsertArray($ownerId);
		$id       = $this->playerRepository->insertPlayer($saveData);
		if ($id === 0)
			throw new ModuleException('player_index', 'Failed to insert local player');

		return $this->playerEntityFactory->create($saveData, $this->userAgentHandler);
	}

	/**
	 * @throws Exception
	 */
	public function fetchDatabase(): PlayerEntity
	{
		$result = $this->playerRepository->findPlayerByUuid($this->userAgentHandler->getUuid());

		return  $this->playerEntityFactory->create($result, $this->userAgentHandler);
	}

	private function buildInsertArray(int $ownerId = 1): array
	{
		return [
			'uuid'        => $this->userAgentHandler->getUuid(),
			'player_name' => $this->userAgentHandler->getName(),
			'firmware'    => $this->userAgentHandler->getFirmware(),
			'model'       => $this->userAgentHandler->getModel()->value,
			'playlist_id' => 0,
			'UID'         => $ownerId,
			'status'      => PlayerStatus::UNRELEASED->value,
			'refresh'     => 900,
			'licence_id'  => 0,
			'commands'    => [],
			'reports'     => [],
			'location_data' => [],
			'location_longitude' => '',
			'location_latitude' => '',
			'categories' => [],
			'properties' => [],
			'remote_administration' => [],
			'screen_times' => []
		];
	}

	private function buildUpdateArray(): array
	{
		return [
			'player_name' => $this->userAgentHandler->getName(),
			'firmware'    => $this->userAgentHandler->getFirmware(),
			'model'       => $this->userAgentHandler->getModel()->value,
			'last_access' => date('Y-m-d H:i:s')
		];
	}

}
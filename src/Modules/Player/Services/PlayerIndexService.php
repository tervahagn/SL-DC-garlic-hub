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


namespace App\Modules\Player\Services;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Player\Repositories\PlayerRepository;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

class PlayerIndexService
{
	private readonly PlayerRepository $playerRepository;
	private readonly LoggerInterface $logger;
	public function __construct(PlayerRepository $playerRepository, LoggerInterface $logger)
	{
		$this->playerRepository = $playerRepository;
		$this->logger = $logger;
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	public function determineForIndexCreation(string $userAgent, int $ownerId): string
	{

		return '';
	}
	private function fetchDatabase(string $uuid): array
	{
		try
		{
			$result = $this->playerRepository->findPlayerByUuid($uuid);

			if (!empty($result['multizone']))
				$result['multizone'] = unserialize($result['multizone']);

			if (empty($result['remote_administration']))
				$result['remote_administration'] = array();
			else
				$result['remote_administration'] = unserialize($result['remote_administration']);

			return  $result;
		}
		catch (Exception $e)
		{
			$this->logger->error($e->getMessage());
			return [];
		}
	}


}
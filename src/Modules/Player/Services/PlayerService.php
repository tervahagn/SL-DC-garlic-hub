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

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Player\Repositories\PlayerRepository;
use App\Modules\Playlists\Helper\PlaylistMode;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;
use Throwable;

class PlayerService extends AbstractBaseService
{
	private readonly PlayerRepository $playerRepository;
	private readonly PlaylistsService $playlistService;
	private readonly AclValidator $playerValidator;

	public function __construct(PlayerRepository $playerRepository, PlaylistsService $playlistService, AclValidator $playerValidator, LoggerInterface $logger)
	{
		$this->playerRepository = $playerRepository;
		$this->playlistService  = $playlistService;
		$this->playerValidator  = $playerValidator;

		parent::__construct($logger);
	}

	/**
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	public function findAllForDashboard(): array
	{
		$ret = $this->playerRepository->findAllForDashboard();
		if (empty($ret))
			$ret = ['active' => 0, 'inactive' => 0, 'pending' => 0];

		return $ret;
	}


	/**
	 * @return array<string,mixed>
	 */
	public function replaceMasterPlaylist(int $playerId, int $playlistId): array
	{
		try
		{
			$this->fetchPlayer($playerId);
			$playlistName = '';
			if ($playlistId > 0)
			{
				$this->playlistService->setUID($this->UID);
				$playlist = $this->playlistService->loadPureById($playlistId);
				if ($playlist['playlist_mode'] !==  PlaylistMode::MASTER->value && $playlist['playlist_mode'] !==  PlaylistMode::MULTIZONE->value)
					throw new ModuleException('player', $playlist['playlist_name'] . ' is not a master playlist');

				$playlistName = $playlist['playlist_name'];
			}
			$affected = $this->playerRepository->update($playerId, ['playlist_id' => $playlistId]);

			return ['affected' => $affected, 'playlist_name' => $playlistName];
		}
		catch (Throwable $e)
		{
			$this->logger->error($e->getMessage());
			$this->addErrorMessage($e->getMessage());
			return [];
		}

	}

	/**
	 * @return array<string,mixed>
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException|ModuleException
	 */
	public function fetchPlayer(int $playerId): array
	{
		$player = $this->playerRepository->findFirstById($playerId);
		if (!$this->playerValidator->isPlayerEditable($this->UID, $player))
			throw new ModuleException('player', 'Error loading player: Is not editable');

		return $player;
	}

}
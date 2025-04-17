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

namespace App\Modules\Playlists\Services;

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class PlaylistsService extends AbstractBaseService
{
	private readonly PlaylistsRepository $playlistsRepository;
	private readonly ExportService $exportService;
	private readonly AclValidator $aclValidator;

	public function __construct(PlaylistsRepository $playlistsRepository, ExportService $exportService, AclValidator $aclValidator, LoggerInterface $logger)
	{
		$this->playlistsRepository = $playlistsRepository;
		$this->exportService       = $exportService;
		$this->aclValidator        = $aclValidator;
		parent::__construct($logger);
	}

	/**
	 * @throws Exception
	 */
	public function createNew($postData): int
	{
		$saveData = $this->collectDataForInsert($postData);
		// No acl checks required as every logged user can create playlists
		return $this->playlistsRepository->insert($saveData);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function toggleShuffle(int $playlistId): int
	{
		$playlist   = $this->playlistsRepository->findFirstWithUserName($playlistId);

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error updating playlist. '.$playlist['playlist_name'].' is not editable');
			throw new ModuleException('playlists', 'Error updating playlist. '.$playlist['playlist_name'].' is not editable');
		}

		if ($playlist['shuffle'] === 0)
			$saveData['shuffle'] = 1;
		else
			$saveData['shuffle'] = 0;

		return $this->update($playlistId, $saveData);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function shufflePicking(int $playlistId, int $shufflePicking = 0): int
	{
		$playlist = $this->playlistsRepository->findFirstWithUserName($playlistId);

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error updating playlist. ' . $playlist['playlist_name'] . ' is not editable');
			throw new ModuleException('playlists', 'Error updating playlist. ' . $playlist['playlist_name'] . ' is not editable');
		}

		$saveData['shuffle_picking'] = $shufflePicking;

		return $this->update($playlistId, $saveData);
	}

	public function exportToSmil(int $playlistId): int
	{
		$playlist = $this->playlistsRepository->findFirstWithUserName($playlistId);

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error updating playlist. ' . $playlist['playlist_name'] . ' is not editable');
			throw new ModuleException('playlists', 'Error updating playlist. ' . $playlist['playlist_name'] . ' is not editable');
		}


	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function updateSecure(array $postData): int
	{
		$playlistId = $postData['playlist_id'];
		$playlist   = $this->playlistsRepository->findFirstWithUserName($playlistId);

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error updating playlist. '.$playlist['playlist_name'].' is not editable');
			throw new ModuleException('playlists', 'Error updating playlist. '.$playlist['playlist_name'].' is not editable');
		}

		$saveData = $this->collectDataForUpdate($postData);

		return $this->update($playlistId, $saveData);
	}

	/**
	 * @throws Exception
	 */
	public function update(int $playlistId, array $saveData): int
	{
		return $this->playlistsRepository->update($playlistId, $saveData);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function delete(int $playlistId): int
	{
		$playlist = $this->playlistsRepository->findFirstWithUserName($playlistId);

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error delete playlist. '.$playlist['playlist_name'].' is not editable');
			throw new ModuleException('playlists', 'Error delete playlist. '.$playlist['playlist_name'].' is not editable');
		}

		return $this->playlistsRepository->delete($playlistId);
	}

	public function loadPlaylistForMultizone(int $playlistId): array
	{
		try
		{
			$playlist = $this->playlistsRepository->findFirstWithUserName($playlistId);
			if (empty($playlist))
				throw new ModuleException('playlists', 'Error loading playlist. Playlist with Id: '.$playlistId.' not found');

			if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
				throw new ModuleException('playlists', 'Error loading playlist: Is not editable');

			if (!empty($playlist['multizone']))
				return unserialize($playlist['multizone']);

			return [];
		}
		catch(\Exception | Exception $e)
		{
			$this->logger->error($e->getMessage());
			$this->addErrorMessage($e->getMessage());
			return [];
		}
	}

	public function loadNameById(int $playlistId): array
	{
		try
		{
			$playlist = $this->playlistsRepository->findFirstBy(['playlist_id' =>$playlistId]);
			if (empty($playlist))
				throw new ModuleException('playlists', 'Error loading playlist. Playlist with Id: '.$playlistId.' not found');

			return array('playlist_id' => $playlistId, 'name' => $playlist['playlist_name']);
		}
		catch(\Exception | Exception $e)
		{
			$this->logger->error($e->getMessage());
			$this->addErrorMessage($e->getMessage());
			return [];
		}
	}

	public function saveZones(int $playlistId, $zones): int
	{
		try
		{
			$count = 0;
			$playlist = $this->playlistsRepository->findFirstWithUserName($playlistId);
			if (empty($playlist))
				throw new ModuleException('playlists', 'Error loading playlist. Playlist with Id: '.$playlistId.' not found');

			if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
				throw new ModuleException('playlists', 'Error loading playlist: Is not editable');

			if (!empty($zones))
				$count = $this->playlistsRepository->update($playlistId, ['multizone' => serialize($zones)]);

			return $count;
		}
		catch(\Exception | Exception $e)
		{
			$this->logger->error($e->getMessage());
			$this->addErrorMessage($e->getMessage());
			return 0;
		}
	}

	/**
	 * @throws Exception
	 */
	public function loadPlaylistForEdit(int $playlistId): array
	{
		try
		{
			$playlist = $this->playlistsRepository->findFirstWithUserName($playlistId);
			if (empty($playlist))
				throw new ModuleException('playlists', 'Error loading playlist. Playlist with Id: '.$playlistId.' not found');

			if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
				throw new ModuleException('playlists', 'Error loading playlist: Is not editable');

			return $playlist;
		}
		catch(\Exception $e)
		{
			$this->logger->error($e->getMessage());
			$this->addErrorMessage($e->getMessage());
			return [];
		}
	}

	public function findAllByItemsAsPlaylistAndMediaId(mixed $fileResource): array
	{
		return $this->playlistsRepository->findAllByItemsAsPlaylistAndMediaId($fileResource);
	}

	private function collectDataForInsert(array $postData): array
	{
		if (array_key_exists('UID', $postData))
			$saveData['UID'] = $postData['UID'];
		else
			$saveData['UID'] = $this->UID;

		$saveData['playlist_mode'] = $postData['playlist_mode'];

		return $this->collectCommon($postData, $saveData);
	}

	private function collectDataForUpdate(array $postData): array
	{
		$saveData = [];
		// only moduleadmin are allowed to change UID
		if (array_key_exists('UID', $postData))
			$saveData['UID'] = $postData['UID'];

		return $this->collectCommon($postData, $saveData);
	}

	private function collectCommon(array $postData, array $saveData): array
	{
		if (isset($postData['playlist_name']))
			$saveData['playlist_name'] = $postData['playlist_name'];

		if (isset($postData['time_limit']))
			$saveData['time_limit'] = $postData['time_limit'];

		if (isset($postData['multizone']))
			$saveData['multizone'] = $postData['multizone'];

		return $saveData;
	}
}
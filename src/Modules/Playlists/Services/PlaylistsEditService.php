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
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class PlaylistsEditService
{
	private readonly PlaylistsRepository $playlistsRepository;
	private readonly AclValidator $aclValidator;
	private readonly LoggerInterface $logger;
	private int $UID;

	public function __construct(PlaylistsRepository $playlistsRepository, AclValidator $aclValidator, LoggerInterface $logger)
	{
		$this->playlistsRepository = $playlistsRepository;
		$this->aclValidator = $aclValidator;
		$this->logger = $logger;
	}

	public function setUID(int $UID): void
	{
		$this->UID = $UID;
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
	public function update(array $postData): int
	{
		$playlistId = $postData['playlist_id'];
		$playlist = $this->playlistsRepository->getFirstDataSet($this->playlistsRepository->findById($playlistId));

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error updating playlist. '.$playlist['name'].' is not editable');
			throw new ModuleException('mediapool', 'Error updating playlist. '.$playlist['name'].' is not editable');
		}

		$saveData = $this->collectDataForUpdate($postData);

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
		$playlist = $this->playlistsRepository->getFirstDataSet($this->playlistsRepository->findById($playlistId));

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error delete playlist. '.$playlist['name'].' is not editable');
			throw new ModuleException('mediapool', 'Error delete playlist. '.$playlist['name'].' is not editable');
		}

		return $this->playlistsRepository->delete($playlistId);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function loadPlaylistForEdit(int $playlistId): array
	{
		$playlist = $this->playlistsRepository->findFirstWithUserName($playlistId);

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error loading playlist. '.$playlist['name'].' is not editable');
			throw new ModuleException('mediapool', 'Error Loading Playlist. '.$playlist['name'].' is not editable');
		}

		return $playlist;
	}

	/**
	 */
	private function collectDataForInsert(array $postData): array
	{
		if (array_key_exists('UID', $postData))
			$saveData['UID'] = $postData['UID'];
		else
			$saveData['UID'] = $this->UID;

		$saveData['playlist_mode'] = $postData['playlist_mode'];

		return $this->collectCommon($postData, $saveData);
	}

	/**
	 */
	private function collectDataForUpdate(array $postData): array
	{
		$saveData = [];
		// only moduleadmin are allowed to change UID
		if (array_key_exists('UID', $postData))
			$saveData['UID'] = $postData['UID'];

		return $this->collectCommon($postData, $saveData);
	}

	/**
	 */
	private function collectCommon(array $postData, array $saveData): array
	{
		$saveData['playlist_name'] = $postData['playlist_name'];
		if (array_key_exists('time_limit', $postData))
			$saveData['time_limit'] = $postData['time_limit'];

		if (array_key_exists('multizone', $postData))
			$saveData['multizone'] = $postData['multizone'];

		return $saveData;
	}

}
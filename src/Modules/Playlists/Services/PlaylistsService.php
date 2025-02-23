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
use App\Modules\Playlists\PlaylistMode;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class PlaylistsService
{


	private readonly PlaylistsRepository $playlistRepository;
	private readonly AclValidator $aclValidator;
	private readonly LoggerInterface $logger;
	private int $UID;

	/**
	 * @param PlaylistsRepository $playlistRepository
	 * @param \App\Modules\Playlists\Services\AclValidator $aclValidator
	 * @param LoggerInterface $logger
	 */
	public function __construct(PlaylistsRepository $playlistRepository, AclValidator $aclValidator, LoggerInterface $logger)
	{
		$this->playlistRepository = $playlistRepository;
		$this->aclValidator = $aclValidator;
		$this->logger = $logger;
	}

	public function setUID(int $UID): void
	{
		$this->UID = $UID;
	}

	/**
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 */
	public function create($postData): int
	{
		$saveData = $this->validatePostDataForInsert($postData);

		// No acl checks required as every logged user can create playlists
		return $this->playlistRepository->insert($saveData);
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
		$playlist = $this->playlistRepository->getFirstDataSet($this->playlistRepository->findById($playlistId));

		$saveData = $this->validatePostDataForUpdate($postData, $playlist);

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error updating playlist. '.$playlist['name'].' is not editable');
			throw new ModuleException('mediapool', 'Error updating playlist. '.$playlist['name'].' is not editable');
		}

		return $this->playlistRepository->update($playlistId, $saveData);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function delete(int $playlistId): int
	{
		$playlist = $this->playlistRepository->getFirstDataSet($this->playlistRepository->findById($playlistId));

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error delete playlist. '.$playlist['name'].' is not editable');
			throw new ModuleException('mediapool', 'Error delete playlist. '.$playlist['name'].' is not editable');
		}

		return $this->playlistRepository->delete($playlistId);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function loadPlaylistForEdit(int $playlistId): array
	{
		$playlist = $this->playlistRepository->findFirstWithUserName($playlistId);

		if (!$this->aclValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error loading playlist. '.$playlist['name'].' is not editable');
			throw new ModuleException('mediapool', 'Error Loading Playlist. '.$playlist['name'].' is not editable');
		}

		return $playlist;
	}

	/**
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function validatePostDataForInsert(array $postData): array
	{
		// only moduleadmin are allowed to change UID
		if (array_key_exists('UID', $postData) && $this->isAdmin())
			$saveData['UID'] = $postData['UID'];
		else
			$saveData['UID'] = $this->UID;


		return $this->validateCommon($postData, $postData['playlist_mode'], $saveData);
	}

	/**
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function validatePostDataForUpdate(array $postData, $oldPlaylist): array
	{
		$saveData = [];
		// only moduleadmin are allowed to change UID
		if (array_key_exists('UID', $postData) && $this->isAdmin())
			$saveData['UID'] = $postData['UID'];

		return $this->validateCommon($postData, $oldPlaylist['playlist_mode'], $saveData);
	}

	private function checkForTimeLimit($value): bool
	{
		return in_array($value, [PlaylistMode::MASTER->value, PlaylistMode::INTERNAL->value], true);
	}

	/**
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function isAdmin(): bool
	{
		return $this->aclValidator->isModuleadmin($this->UID) || $this->aclValidator->isSubAdmin($this->UID);
	}

	/**
	 * @param array $postData
	 * @param $playlist_mode
	 * @param array $saveData
	 * @return array
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 */
	private function validateCommon(array $postData, $playlist_mode, array $saveData): array
	{
		$saveData['name'] = $postData['playlist_name'];

		if (array_key_exists('time_limit', $postData) && $this->isAdmin() && $this->checkForTimeLimit($playlist_mode))
		{
			$saveData['time_limit'] = $postData['time_limit'];
		}

		if (array_key_exists('multizone', $postData) && $playlist_mode === PlaylistMode::MULTIZONE->value)
		{
			$saveData['multizone'] = $postData['multizone'];
		}

		return $saveData;
	}

}
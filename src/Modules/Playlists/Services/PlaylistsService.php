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

class PlaylistsService
{


	private readonly PlaylistsRepository $playlistRepository;
	private readonly AclValidator $playlistValidator;
	private readonly LoggerInterface $logger;
	private int $UID;

	/**
	 * @param PlaylistsRepository $playlistRepository
	 * @param AclValidator $playlistValidator
	 * @param LoggerInterface $logger
	 */
	public function __construct(PlaylistsRepository $playlistRepository, AclValidator $playlistValidator, LoggerInterface $logger)
	{
		$this->playlistRepository = $playlistRepository;
		$this->playlistValidator = $playlistValidator;
		$this->logger = $logger;
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws CoreException
	 * @throws Exception
	 */
	public function isModuleadmin(): bool
	{
		return $this->playlistValidator->isModuleadmin($this->UID);
	}

	public function setUID(int $UID): void
	{
		$this->UID = $UID;
	}

	/**
	 * @throws Exception
	 */
	public function create($playlistData): int
	{
		// No check neccessary as everyone shound create playlists
		return $this->playlistRepository->insert($playlistData);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function update(int $playlistId, array $playlistData): int
	{
		$playlist = $this->playlistRepository->getFirstDataSet($this->playlistRepository->findById($playlistId));

		if ($this->playlistValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error updating playlist. '.$playlist['name'].' is not editable');
			throw new ModuleException('mediapool', 'Error updating playlist. '.$playlist['name'].' is not editable');
		}

		return $this->playlistRepository->update($playlistId, $playlistData);
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

		if ($this->playlistValidator->isPlaylistEditable($this->UID, $playlist))
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
		$playlist = $this->playlistRepository->getFirstDataSet($this->playlistRepository->findById($playlistId));

		if ($this->playlistValidator->isPlaylistEditable($this->UID, $playlist))
		{
			$this->logger->error('Error loading playlist. '.$playlist['name'].' is not editable');
			throw new ModuleException('mediapool', 'Error Loading Playlist. '.$playlist['name'].' is not editable');
		}

		return $playlist;
	}


}
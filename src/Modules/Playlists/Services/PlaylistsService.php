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

use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Psr\Log\LoggerInterface;

class PlaylistsService
{
	private readonly PlaylistsRepository $playlistRepository;
	private readonly AclValidator $playlistValidator;
	private readonly LoggerInterface $logger;

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

	public function create($playlistData): int
	{
		$this->playlistValidator->hasRights();
		return $this->playlistRepository->insert($playlistData);
	}

}
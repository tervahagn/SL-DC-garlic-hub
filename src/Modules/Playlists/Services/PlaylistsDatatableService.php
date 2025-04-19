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
use App\Framework\Services\AbstractDatatableService;
use App\Framework\Services\SearchFilterParamsTrait;
use App\Framework\Utils\FormParameters\BaseParameters;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class PlaylistsDatatableService extends AbstractDatatableService
{
	use SearchFilterParamsTrait;
	private readonly PlaylistsRepository $playlistsRepository;
	private readonly AclValidator $aclValidator;
	private BaseParameters $parameters;

	public function __construct(PlaylistsRepository $playlistsRepository, BaseParameters $parameters, AclValidator $aclValidator, LoggerInterface $logger)
	{
		$this->playlistsRepository = $playlistsRepository;
		$this->aclValidator = $aclValidator;
		$this->parameters = $parameters;
		parent::__construct($logger);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function loadDatatable(): void
	{
		if ($this->aclValidator->isModuleAdmin($this->UID))
		{
			$this->fetchForModuleAdmin($this->playlistsRepository, $this->parameters);
		}
		elseif ($this->aclValidator->isSubAdmin($this->UID))
		{
	//		$this->handleRequestSubAdmin($this->playlistsRepository);
		}
		elseif ($this->aclValidator->isEditor($this->UID))
		{
			// Todo
		}
		elseif ($this->aclValidator->isViewer($this->UID))
		{
			// Todo
		}
		else
		{
			$this->fetchForUser($this->playlistsRepository, $this->parameters);
		}
	}


	public function getPlaylistsInUse(array $playlistIds): array
	{
		if (empty($playlistIds))
			return [];

		return $this->arePlayListsInUse($playlistIds);
	}


	protected function arePlayListsInUse(array $playlistIds): array
	{
		$results = [];
		/* later
			// Todo: Find some smarter way to this
			foreach($this->playerRepository->findPlaylistIdsByPlaylistIds($playlistIds) as $value)
			{
				$results[$value['playlist_id']] = true;
			}
			foreach($this->itemRepository->findMediaIdsByPlaylistId($playlistIds) as $value)
			{
				$results[$value['media_id']] = true;
			}
	*/
		/* no channels currently
			foreach($this->channelRepository->findTableIdsByPlaylistIds($playlistIds) as $value)
			{
				$results[$value['table_id']] = true;
			}
			*/

		return $results;
	}

}
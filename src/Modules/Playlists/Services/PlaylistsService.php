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

use App\Framework\Database\BaseRepositories\FilterBase;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Services\AbstractBaseService;
use App\Framework\Services\SearchFilterParamsTrait;
use App\Framework\Utils\FormParameters\BaseParameters;
use App\Modules\Playlists\Repositories\PlaylistsRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class PlaylistsService extends AbstractBaseService
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

	public function loadDatatable(): void
	{
		if ($this->aclValidator->isModuleAdmin($this->UID))
		{
			$this->handleRequestModuleAdmin($this->playlistsRepository);
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
			$this->handleRequestUser($this->playlistsRepository);
		}

	}

	/**
	 * @throws Exception
	 */
	public function handleRequestModuleAdmin(FilterBase $repository): static
	{
		// later		$this->setCompanyArray($this->getUser()->getAllCompanyIds());
		// for edge
		$this->setCompanyArray([[1 => 'local']]);

		$this->setAllowedCompanyIds(array_keys($this->getCompanyArray()));

		$total_elements 	   = $repository->countAllFiltered($this->parameters->getInputParametersArray());
		$results	           = $repository->findAllFiltered($this->parameters->getInputParametersArray());

		return $this->setAllResultData($total_elements,  $results);
	}

/*	public function handleRequestSubAdmin(FilterBase $repository): static
	{
		// companies to show names in dropdowns e.g.
	/*	$this->setCompanyArray($this->getUser()->getAllCompanyIds());

		$company_ids = $this->aclValidator->determineCompaniesForSubAdmin();
		$this->setAllowedCompanyIds($company_ids);

		$total_elements = $repository->countAllFilteredByUIDCompanyReseller(
			$company_ids,
			$parameters->getInputParametersArray(),
			$this->getUser()->getUID()
		);

		$results = $repository->findAllFilteredByUIDCompanyReseller(
			$company_ids,
			$parameters->getInputParametersArray(),
			$this->getUser()->getUID()
		);
		return $this->setAllResultData($total_elements,  $results);
		return $this;
	}

*/
	public function handleRequestUser(FilterBase $repository): static
	{
		$total_elements = $repository->countAllFilteredByUID($this->parameters->getInputParametersArray(), $this->UID);
		$results        = $repository->findAllFilteredByUID($this->parameters->getInputParametersArray(), $this->UID);

		return $this->setAllResultData($total_elements, $results);
	}

	public function getPlaylistsInUse(array $playlistIds)
	{
		if (empty($playlistIds))
			return [];

		return $this->arePlayListsInUse($playlistIds);
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
			$this->logger->error('Error updating playlist. '.$playlist['playlist_name'].' is not editable');
			throw new ModuleException('playlists', 'Error updating playlist. '.$playlist['playlist_name'].' is not editable');
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

	protected function arePlayListsInUse(array $playlistIds)
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
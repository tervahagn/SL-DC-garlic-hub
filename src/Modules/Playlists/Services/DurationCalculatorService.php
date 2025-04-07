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
use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Modules\Playlists\Repositories\ItemsRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;

/**
 * class for calculating and manipulating durations of
 * playlist
 */
class DurationCalculatorService
{
	private ItemsRepository $itemsRepository;
	private AclValidator $aclValidator;
	private Config $config;
	private int $UID;
	private int $totalEntries;
	private int $ownerDuration;
	private int $duration;
	private int $fileSize;

	public function __construct(ItemsRepository $itemsRepository, AclValidator $aclValidator, Config $config)
	{
		$this->itemsRepository     = $itemsRepository;
		$this->aclValidator     = $aclValidator;
		$this->config           = $config;
	}

	public function getTotalEntries(): int
	{
		return $this->totalEntries;
	}

	public function getDuration(): int
	{
		return $this->duration;
	}

	public function getOwnerDuration(): int
	{
		return $this->ownerDuration;
	}

	public function getFileSize(): int
	{
		return $this->fileSize;
	}

	public function setUID(int $UID): static
	{
		$this->UID = $UID;
		return $this;
	}

	public function reset(): static
	{
		$this->totalEntries  = 0;
		$this->fileSize      = 0;
		$this->duration      = 0;
		$this->ownerDuration = 0;
		return $this;
	}

	/**
	 * @throws Exception
	 */
	public function determineTotalPlaylistProperties($playlistId): int
	{
		$result = $this->itemsRepository->sumAndCountByPlaylistId($playlistId);
		$this->fileSize     = $result['totalSize'];
		$this->totalEntries = $result['totalEntries'];

		return $this->getFileSize();
	}

	/**
	 * use self::getDuration() and self::getOwnerDuration()
	 * to get the result
	 *
	 * @param array $playlistData
	 * @return $this
	 * @throws CoreException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function calculatePlaylistDurationFromItems(array $playlistData): static
	{
		// nothing to do?
		// sometimes we get an item just with default values, but no playlist id, so ignore them as well
		if (empty($playlistData) || !isset($playlistData['playlist_id']))
		{
			$this->reset();
			return $this;
		}

		$this->duration     = $this->itemsRepository->sumDurationOfEnabledByPlaylistId($playlistData['playlist_id']);
		if ($playlistData['shuffle'] > 0 &&	$playlistData['shuffle_picking'] > 0) // check if playlist is shuffle with pickup
			$this->duration = floor($this->duration / $playlistData['shuffle_picking']);

		if ($playlistData['time_limit'] == 0 &&	($this->aclValidator->isSimpleAdmin($this->UID)))
			$this->ownerDuration = $this->duration;
		else
			$this->ownerDuration = $this->itemsRepository->sumDurationOfItemsByUIDAndPlaylistId($this->UID, $playlistData['playlist_id']);

		return $this;
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function calculateRemainingItemDuration(array $playlist, array $media = [])
	{
		$default_duration = $this->config->getConfigValue('duration', 'playlists', 'Defaults');

		// only videos have a duration > 0. Using the constant for other media.
		// this is only used, when inserting new items

		$duration = (array_key_exists('duration', $media['metadata']) && $media['metadata']['duration'] > 0) ? $media['metadata']['duration'] : $default_duration;

		if ($playlist['time_limit'] > 0 && !$this->aclValidator->isSimpleAdmin($this->UID))
		{
			$this->calculatePlaylistDurationFromItems($playlist);
			$remaining_duration = $playlist['time_limit'] - $this->getOwnerDuration();

			if ($remaining_duration <= 0)
				$duration = 0;
			elseif ($remaining_duration < $duration)
				$duration = $remaining_duration;
		}

		return $duration;
	}

}





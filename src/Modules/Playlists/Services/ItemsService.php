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
use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Helper\ItemType;
use App\Modules\Playlists\Repositories\ItemsRepository;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class ItemsService extends AbstractBaseService
{
	private readonly ItemsRepository $itemsRepository;
	private readonly PlaylistsService $playlistsService;
	private readonly MediaService $mediaService;#
	private readonly PlaylistMetricsCalculator $playlistMetricsCalculator;

	public function __construct(ItemsRepository $itemsRepository,
								MediaService $mediaService,
								PlaylistsService $playlistsService,
								PlaylistMetricsCalculator $playlistMetricsCalculator,
								LoggerInterface $logger)
	{
		$this->itemsRepository  = $itemsRepository;
		$this->playlistsService = $playlistsService;
		$this->mediaService     = $mediaService;
		$this->playlistMetricsCalculator = $playlistMetricsCalculator;

		parent::__construct($logger);
	}

	/**
	 * @throws Exception
	 */
	public function loadByPlaylistForExport(array $playlist, string $edition): array
	{
		$items   = [];
		$results = $this->itemsRepository->findAllByPlaylistIdWithJoins($playlist['playlist_id'], $edition);
		foreach ($results as $item)
		{
			$item['conditional']   = $this->sanitize($item['conditional']);
			$item['properties']    = $this->sanitize($item['properties']);
			$item['categories']    = $this->sanitize($item['categories']);
			$item['content_data']  = $this->sanitize($item['content_data']);
			$item['begin_trigger'] = $this->sanitize($item['begin_trigger']);
			$item['end_trigger']   = $this->sanitize($item['end_trigger']);
			$items[] = $item;
		}

		$properties = $this->playlistMetricsCalculator
			->reset()
			->calculateFromItems($playlist, $results)
			->getMetricsForPlaylistTable();

		return ['properties' => $properties, 'items' => $items];
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function loadItemsByPlaylistIdForComposer(int $playlistId): array
	{
		$this->playlistsService->setUID($this->UID);

		$items = [];
		$thumbnailPath  = $this->mediaService->getPathTumbnails();
		$result = $this->itemsRepository->findAllByPlaylistId($playlistId);
		foreach($result as $value)
		{
			switch ($value['item_type'])
			{
				case ItemType::MEDIAPOOL->value:
					$tmp = $value;
					if (str_starts_with($value['mimetype'], 'image/'))
						$ext = str_replace('jpeg', 'jpg', substr(strrchr($value['mimetype'], '/'), 1));
					else
						$ext = 'jpg';

					$tmp['paths']['thumbnail'] = $thumbnailPath.'/'.$value['file_resource'].'.'.$ext;
					$items[] = $tmp;
					break;
				case ItemType::PLAYLIST->value:
					$tmp = $value;
					$tmp['paths']['thumbnail'] = 'public/images/icons/playlist.svg';
					$items[] = $tmp;
					break;

			}
		}
		$playlist = $this->playlistsService->loadPureById($playlistId);
		$playlist_metrics = $this->playlistMetricsCalculator->calculateFromItems($playlist, $result)->getMetricsForFrontend();

		return ['playlist_metrics' =>  $playlist_metrics, 'items' => $items];
	}

	/**
	 * @throws Exception
	 */
	public function insertMedia(int $playlistId, string $id, int $position): array
	{
		try
		{
			$this->itemsRepository->beginTransaction();
			$this->mediaService->setUID($this->UID);
			$playlistData = $this->checkPlaylistAcl($playlistId);

			$media = $this->mediaService->fetchMedia($id); // checks rights, too
			if (empty($media))
				throw new ModuleException('items', 'Media is not accessible');

/*			if (!$this->allowedByTimeLimit($playlistId, $playlistData['time_limit']))
				throw new ModuleException('items', 'Playlist time limit exceeds');
*/
			$itemDuration =  $this->playlistMetricsCalculator->calculateRemainingMediaDuration($playlistData, $media);
			$this->itemsRepository->updatePositionsWhenInserted($playlistId, $position);
			$saveItem = [
				'playlist_id'   => $playlistId,
				'datasource'    => 'file',
				'UID'           => $this->UID,
				'item_duration' => $itemDuration,
				'item_filesize' => $media['metadata']['size'],
				'item_order'    => $position,
				'item_name'     => $media['filename'],
				'item_type'     => ItemType::MEDIAPOOL->value,
				'file_resource' => $media['checksum'],
				'mimetype'      => $media['mimetype'],
			];
			$id = $this->itemsRepository->insert($saveItem);
			if ($id === 0)
				throw new ModuleException('items', 'Playlist item could not inserted.');

			$saveItem['item_id'] = $id;
			$saveItem['paths'] = $media['paths'];

			$playlistMetrics = $this->updateCurrentPlaylistMetrics($playlistData);
			// required before recursion otherwise it will overwrite current PlaylistsMetrics
			$this->updatePlaylistMetricsRecursively($playlistData['playlist_id']);

			$this->itemsRepository->commitTransaction();

			return ['playlist_metrics' => $playlistMetrics, 'item' => $saveItem];
		}
		catch (Exception | ModuleException | CoreException | PhpfastcacheSimpleCacheException $e)
		{
			$this->itemsRepository->rollBackTransaction();
			$this->logger->error('Error insert media: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * @throws Exception
	 */
	public function insertPlaylist(int $targetId, string $insertId, int $position): array
	{
		try
		{
			$this->itemsRepository->beginTransaction();
			$this->mediaService->setUID($this->UID);

			$playlistTargetData = $this->checkPlaylistAcl($targetId);
			$playlistInsertData = $this->checkPlaylistAcl($insertId);

/*			if (!$this->allowedByTimeLimit($targetId, $playlistTargetData['time_limit']))
				throw new ModuleException('items', 'Playlist time limit exceeds');
*/
			$this->itemsRepository->updatePositionsWhenInserted($targetId, $position);
			$saveItem = [
				'playlist_id'   => $targetId,
				'datasource'    => 'file',
				'UID'           => $this->UID,
				'item_duration' => $playlistInsertData['duration'],
				'item_filesize' => $playlistInsertData['filesize'],
				'item_order'    => $position,
				'item_name'     => $playlistInsertData['playlist_name'],
				'item_type'     => ItemType::PLAYLIST->value,
				'file_resource' => $insertId,
				'mimetype'      => ''
			];
			$id = $this->itemsRepository->insert($saveItem);
			if ($id === 0)
				throw new ModuleException('items', 'Playlist item could not inserted.');

			$saveItem['item_id'] = $id;
			$saveItem['paths']['thumbnail'] = 'public/images/icons/playlist.svg';

			$playlistMetrics = $this->updateCurrentPlaylistMetrics($playlistTargetData);
			// required before recursion otherwise it will overwrite current PlaylistsMetrics
			$this->updatePlaylistMetricsRecursively($playlistTargetData['playlist_id']);

			$this->itemsRepository->commitTransaction();

			return ['playlist_metrics' => $playlistMetrics, 'item' => $saveItem];
		}
		catch (Exception | ModuleException | CoreException | PhpfastcacheSimpleCacheException $e)
		{
			$this->itemsRepository->rollBackTransaction();
			$this->logger->error('Error insert media: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * @throws Exception
	 */
	public function updateItemOrder(mixed $playlist_id, array $itemsOrder): void
	{
		$this->playlistsService->setUID($this->UID);
		$this->playlistsService->loadPlaylistForEdit($playlist_id); // will check for rights

		foreach ($itemsOrder as $key => $itemId)
		{
			$this->itemsRepository->updateItemOrder($itemId, $key);
		}
	}

	/**
	 * @throws Exception
	 */
	public function delete(int $playlistId, int $itemId): array
	{
		try
		{
			$this->itemsRepository->beginTransaction();

			$playlistData = $this->checkPlaylistAcl($playlistId);
			$item         = $this->itemsRepository->findFirstBy(['item_id' => $itemId]);
			if (empty($item))
				throw new ModuleException('items', 'Item not found');

			// todo for Core / Enterprise: Check if item belongs to an admin

			$deleteId = $this->itemsRepository->delete($itemId);
			if ($deleteId === 0)
				throw new ModuleException('items', 'Item could not deleted');

			$this->itemsRepository->updatePositionsWhenDeleted($playlistId, $item['item_order']);

			$playlistMetrics = $this->updateCurrentPlaylistMetrics($playlistData);
			// required before recursion otherwise it will overwrite current PlaylistsMetrics
			$this->updatePlaylistMetricsRecursively($playlistData['playlist_id']);

			$this->itemsRepository->commitTransaction();

			return ['playlist_metrics' => $playlistMetrics, 'delete_id' => $deleteId];
		}
		catch (Exception | ModuleException | CoreException | PhpfastcacheSimpleCacheException $e)
		{
			$this->itemsRepository->rollBackTransaction();
			$this->logger->error('Error insert media: ' . $e->getMessage());
			return [];
		}

	}

	/**
	 * @throws Exception
	 */
	private function updateCurrentPlaylistMetrics(array $playlistData): array
	{
		$this->playlistsService->update(
			$playlistData['playlist_id'],
			$this->playlistMetricsCalculator->calculateFromPlaylistData($playlistData)->getMetricsForPlaylistTable()
		);
		return $this->playlistMetricsCalculator->getMetricsForFrontend();

	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	private function updatePlaylistMetricsRecursively(int $playlistId): void
	{
		$tmp = $this->playlistsService->findAllPlaylistsWhichIncludedThisPlaylistAsItem($playlistId);
		foreach($tmp as $playlistData)
		{
			$this->updateMetrics($playlistData);
			$this->updatePlaylistMetricsRecursively($playlistData['playlist_id']);
		}
	}

	/**
	 * @throws Exception
	 */
	private function updateMetrics($playlistData): void
	{
		$this->playlistsService->update(
			$playlistData['playlist_id'],
			$this->playlistMetricsCalculator->calculateFromPlaylistData($playlistData)->getMetricsForPlaylistTable()
		);

		// update the item dataset
		$saveItem = [
			'item_duration'     => $this->playlistMetricsCalculator->getDuration(),
			'item_filesize'     => $this->playlistMetricsCalculator->getFileSize()
		];
		$this->itemsRepository->update($playlistData['playlist_id'], $saveItem);
	}

	/**
	 * @throws ModuleException
	 */
	private function checkPlaylistAcl(int $playlistId): array
	{
		$this->playlistsService->setUID($this->UID);
		$this->playlistMetricsCalculator->setUID($this->UID);
		$playlistData = $this->playlistsService->loadPlaylistForEdit($playlistId); // also checks rights
		if (empty($playlistData))
			throw new ModuleException('items', 'Playlist is not accessible');

		return $playlistData;
	}

	/**
	 * @throws Exception
	 */
	private function allowedByTimeLimit(int $playlistId, int $timeLimit): bool
	{
	/*	if ($timeLimit > 0)
			return ($this->itemsRepository->sumDurationOfItemsByUIDAndPlaylistId($this->UID, $playlistId) <= $timeLimit);
*/
		return true;
	}

	public function sanitize(string $value): array
	{
		if ($value === '')
			return [];

		return unserialize($value);
	}


}
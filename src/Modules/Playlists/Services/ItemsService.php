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

/**+
 * Todo Concept:
 * Adding, deleting or duration change pflaylist needs to check for time limit directly
 * Exporting will set recursively item times and should stop, if a higher leveled playlist time limit will exceed
 */
class ItemsService extends AbstractBaseService
{
	private readonly ItemsRepository $itemsRepository;
	private readonly PlaylistsService $playlistsService;
	private readonly MediaService $mediaService;
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

	public function getItemsRepository(): ItemsRepository
	{
		return $this->itemsRepository;
	}

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

		$playlistMetrics = $this->playlistMetricsCalculator
			->reset()
			->calculateFromItems($playlist, $results)
			->getMetricsForPlaylistTable();

		return ['playlist_metrics' =>  $playlistMetrics, 'items' => $items];
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

		$playlist = $this->playlistsService->loadPureById($playlistId); // checks rights

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
		$playlistData    = $this->playlistsService->loadPureById($playlistId);
		$playlistMetrics = $this->playlistMetricsCalculator->calculateFromPlaylistData($playlistData)->getMetricsForFrontend();

		return ['playlist_metrics' =>  $playlistMetrics, 'playlist' => $playlist, 'items' => $items];
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

			$playlistMetrics = $this->playlistMetricsCalculator->calculateFromPlaylistData($playlistData)->getMetricsForFrontend();

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
	public function updateItemsMetrics(int $playlistId): void
	{
		$saveItem = [
			'item_duration' => $this->playlistMetricsCalculator->getDuration(),
			'item_filesize' => $this->playlistMetricsCalculator->getFilesize()
		];
		$this->itemsRepository->updateWithWhere($saveItem, ['file_resource' => $playlistId]);
	}

	/**
	 *
	 * 1. Save metrics in exported playlist (did in export-class)
	 * 2. Save metrics in all items wich represent this playlist. (did in export-class)
	 * 3. find all playlists which have the exported playlist nested (here we go)
	 * 4. calculate metrics for the new playlists
	 * 5. save metrics in new playlists
	 * 6. save metrics in all items which represent the new playlist
	 * 7. recurse to 3.
	 *
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function updateMetricsRecursively(int $playlistId): void
	{
		$tmp = $this->itemsRepository->findAllPlaylistsContainingPlaylist($playlistId);
		foreach($tmp as $playlistData)
		{
			$this->playlistMetricsCalculator->calculateFromPlaylistData($playlistData);
			$this->updateItemsMetrics($playlistData['playlist_id']);
			$this->updatePlaylistMetrics($playlistData['playlist_id']);

			$this->updateMetricsRecursively($playlistData['playlist_id']);
		}
	}

	/**
	 * @throws Exception
	 */
	private function updatePlaylistMetrics(int $playlistId): void
	{
		$this->playlistsService->update($playlistId, $this->playlistMetricsCalculator->getMetricsForPlaylistTable());
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

	private function sanitize(string $value): array
	{
		if ($value === '')
			return [];

		return unserialize($value);
	}
}
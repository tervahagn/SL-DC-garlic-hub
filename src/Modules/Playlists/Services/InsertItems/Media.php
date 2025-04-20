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

namespace App\Modules\Playlists\Services\InsertItems;

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Helper\ItemType;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Services\PlaylistMetricsCalculator;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class Media extends AbstractInsertItem
{
	private MediaService $mediaService;


	public function __construct(
		ItemsRepository $itemsRepository,
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
	public function insert(int $playlistId, string $insertId, int $position): array
	{
		try
		{
			$this->itemsRepository->beginTransaction();
			$this->mediaService->setUID($this->UID);
			$playlistData = $this->checkPlaylistAcl($playlistId);

			$media = $this->mediaService->fetchMedia($insertId); // checks rights, too
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
			$insertId = $this->itemsRepository->insert($saveItem);
			if ($insertId === 0)
				throw new ModuleException('items', 'Playlist item could not inserted.');

			$saveItem['item_id'] = $insertId;
			$saveItem['paths'] = $media['paths'];

			$playlistMetrics = $this->playlistMetricsCalculator->calculateFromPlaylistData($playlistData)->getMetricsForFrontend();

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

}
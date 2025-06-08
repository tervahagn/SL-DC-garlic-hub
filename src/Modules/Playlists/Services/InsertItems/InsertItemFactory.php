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

use App\Modules\Mediapool\Services\MediaService;
use App\Modules\Playlists\Repositories\ItemsRepository;
use App\Modules\Playlists\Services\PlaylistMetricsCalculator;
use App\Modules\Playlists\Services\PlaylistsService;
use App\Modules\Playlists\Services\WidgetsService;
use Psr\Log\LoggerInterface;

class InsertItemFactory
{
	private readonly MediaService $mediaService;
	protected readonly ItemsRepository $itemsRepository;
	protected readonly PlaylistsService $playlistsService;
	protected readonly PlaylistMetricsCalculator $playlistMetricsCalculator;
	protected readonly WidgetsService $widgetsService;
	protected readonly LoggerInterface $logger;


	public function __construct(MediaService $mediaService,
		ItemsRepository $itemsRepository,
		PlaylistsService $playlistsService,
		PlaylistMetricsCalculator $playlistMetricsCalculator,
		WidgetsService $widgetsService,
		LoggerInterface $logger)
	{
		$this->mediaService = $mediaService;
		$this->itemsRepository = $itemsRepository;
		$this->playlistsService = $playlistsService;
		$this->playlistMetricsCalculator = $playlistMetricsCalculator;
		$this->widgetsService = $widgetsService;
		$this->logger = $logger;
	}

	public function create(string $source): ?AbstractInsertItem
	{
		$item = match ($source)
		{
			'mediapool' => new Media($this->itemsRepository, $this->mediaService, $this->playlistsService, $this->playlistMetricsCalculator, $this->widgetsService, $this->logger),
			'playlist' => new Playlist($this->itemsRepository, $this->playlistsService, $this->playlistMetricsCalculator, $this->logger),
			default => null,
		};
		return $item;
	}

}
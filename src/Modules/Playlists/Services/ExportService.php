<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Helper\ExportSmil\LocalWriter;
use App\Modules\Playlists\Helper\ExportSmil\PlaylistContent;
use App\Modules\Playlists\Repositories\ItemsRepository;
use Doctrine\DBAL\Exception;
use League\Flysystem\FilesystemException;

class ExportService
{
	private Config $config;
	private ItemsRepository $itemsRepository;
	private LocalWriter $localSmilWriter;
	private PlaylistContent $playlistContent;

	public function __construct(Config $config, ItemsRepository $itemsRepository, LocalWriter $localSmilWriter, PlaylistContent $playlistContent)
	{
		$this->config          = $config;
		$this->itemsRepository = $itemsRepository;
		$this->localSmilWriter = $localSmilWriter;
		$this->playlistContent = $playlistContent;

	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws FilesystemException
	 * @throws ModuleException
	 */
	public function export(array $playlist): void
	{
		$items = $this->sanitizeArrays($this->itemsRepository->findAllByPlaylistIdWithJoins($playlist['playlist_id'], $this->config->getEdition()));

		$this->playlistContent->init($playlist, $items)->build();

		$this->localSmilWriter->initExport($playlist['playlist_id']);

		$this->localSmilWriter->writeSMILFiles($this->playlistContent);
	}

	public function sanitizeArrays(array $items): array
	{
		$ar = [];
		foreach ($items as $item)
		{
			$item['conditional']   = $this->sanitize($item['conditional']);
			$item['properties']    = $this->sanitize($item['properties']);
			$item['categories']    = $this->sanitize($item['categories']);
			$item['content_data']  = $this->sanitize($item['content_data']);
			$item['begin_trigger'] = $this->sanitize($item['begin_trigger']);
			$item['end_trigger']   = $this->sanitize($item['end_trigger']);
			$ar[] = $item;
		}

		return $ar;
	}

	public function sanitize(string $value): array
	{
		if ($value === '')
			return [];

		return unserialize($value);
	}


}
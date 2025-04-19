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
use App\Framework\Services\AbstractBaseService;
use App\Modules\Playlists\Helper\ExportSmil\LocalWriter;
use App\Modules\Playlists\Helper\ExportSmil\PlaylistContent;
use App\Modules\Playlists\Helper\PlaylistMode;
use Doctrine\DBAL\Exception;
use League\Flysystem\FilesystemException;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Log\LoggerInterface;

class ExportService extends AbstractBaseService
{
	private Config $config;
	private PlaylistsService $playlistsService;
	private ItemsService $itemsService;
	private LocalWriter $localSmilWriter;
	private PlaylistContent $playlistContent;

	public function __construct(Config $config, PlaylistsService $playlistsService, ItemsService $itemsService, LocalWriter $localSmilWriter, PlaylistContent $playlistContent, LoggerInterface $logger )
	{
		$this->config          = $config;
		$this->playlistsService = $playlistsService;
		$this->itemsService    = $itemsService;
		$this->localSmilWriter = $localSmilWriter;
		$this->playlistContent = $playlistContent;

		parent::__construct($logger);
	}

	public function exportToSmil(int $playlistId): int
	{
		try
		{
			$this->playlistsService->setUID($this->UID);
			$this->itemsService->setUID($this->UID);

			$playlist = $this->playlistsService->loadPureById($playlistId); // checks rightzs

			$count = 0;

			if ($playlist['playlist_mode'] === PlaylistMode::MULTIZONE->value && !empty($playlist['multizone']))
			{
				$zones = unserialize($playlist['multizone']);
				$properties = ['filesize' => 0, 'duration' => 0, 'owner_duration' => 0];
				foreach ($zones as $zone)
				{
					$tmp = $this->export($this->playlistsService->loadPureById($zone['zones']['zone_playlist_id']));
					$count++;
					// use the highest values for a multizone.
					$properties['filesize'] = max($properties['filesize'], $tmp['filesize']);
					$properties['duration'] = max($properties['duration'], $tmp['duration']);
					$properties['owner_duration'] = max($properties['owner_duration'], $tmp['owner_duration']);
				}
			} else
			{
				$properties = $this->export($playlist);
				$count++;
			}
			if ($this->playlistsService->update($playlistId, $properties) === 0)
				throw new ModuleException('export_playlist', 'Export '.$playlistId.' failed. Could not update playlist properties.');


			return $count;
		}
		catch (ModuleException|CoreException|Exception|FilesystemException|PhpfastcacheSimpleCacheException $e)
		{
			$this->logger->error('Error export SMIL playlist: ' . $e->getMessage());
			return 0;
		}
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws FilesystemException
	 * @throws ModuleException
	 */
	public function export(array $playlist): array
	{
		$results = $this->itemsService->loadByPlaylistForExport($playlist, $this->config->getEdition());

		$this->playlistContent->init($playlist, $results['items'])->build();
		$this->localSmilWriter->initExport($playlist['playlist_id']);
		$this->localSmilWriter->writeSMILFiles($this->playlistContent);

		return $results['properties'];
	}

}
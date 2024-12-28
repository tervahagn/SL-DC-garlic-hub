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


namespace App\Modules\Mediapool\Utils;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

class Video extends AbstractMediaHandler
{
	private string $ffmpegPath;

	public function __construct(Config $config, Filesystem $filesystem, string $ffmpegPath)
	{
		$this->ffmpegPath = $ffmpegPath;
		parent::__construct($config, $filesystem);
	}

	/**
	 * @throws ModuleException
	 */
	private function getVideoResolution(string $videoPath): array
	{
		$command = sprintf(
			'%s -i %s 2>&1',
			escapeshellcmd($this->ffmpegPath),
			escapeshellarg($videoPath)
		);

		exec($command, $output, $returnVar);

		if ($returnVar !== 1)
		{
			throw new ModuleException("Fehler beim Lesen der Videodaten: " . implode("\n", $output));
		}

		foreach ($output as $line) {
			if (preg_match('/Stream.*Video.* (\d{2,})x(\d{2,})/', $line, $matches)) {
				return [(int)$matches[1], (int)$matches[2]]; // [width, height]
			}
		}

		throw new ModuleException("Auflösung konnte nicht ermittelt werden.");
	}

	/**
	 * @throws ModuleException
	 * @throws FilesystemException
	 */
	public function createThumbnail(string $videoPath, string $thumbnailPath, int $timeInSeconds = 1): array
	{
		if (!file_exists($videoPath))
		{
			throw new ModuleException('mediapool', "Das Video '$videoPath' existiert nicht.");
		}

		$resolution = $this->getVideoResolution($videoPath);

		// Temporäre Datei für das Thumbnail
		$tempThumbnail = sys_get_temp_dir() . '/' . uniqid('thumb_') . '.jpg';

		$command = sprintf(
			'%s -i %s -ss %d -vframes 1 -q:v 2 -vf scale=%d:%d %s 2>&1',
			escapeshellcmd($this->ffmpegPath),
			escapeshellarg($videoPath),
			$timeInSeconds,
			$resolution[0],
			$resolution[1],
			escapeshellarg($tempThumbnail)
		);

		exec($command, $output, $returnVar);

		if ($returnVar !== 0)
		{
			throw new ModuleException('mediapool',"Fehler beim Erstellen des Thumbnails: " . implode("\n", $output));
		}

		// Thumbnail in Flysystem speichern
		$thumbnailContent = file_get_contents($tempThumbnail);
		if ($thumbnailContent === false)
		{
			throw new ModuleException('mediapool', "Fehler beim Lesen des generierten Thumbnails.");
		}

		$this->filesystem->write($thumbnailPath, $thumbnailContent);

		// Temporäre Datei löschen
		unlink($tempThumbnail);

		return $resolution;
	}
}
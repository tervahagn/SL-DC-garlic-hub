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
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageManagerInterface;
use League\Flysystem\Filesystem;

class MediaHandlerFactory
{
	private Config $config;
	private Filesystem $fileSystem;
	private ImageManagerInterface $imageManager;

	/**
	 * @param Config     $config
	 * @param Filesystem $fileSystem
	 */
	public function __construct(Config $config,
		Filesystem $fileSystem,
		ImageManagerInterface $imageManager

	)
	{
		$this->config = $config;
		$this->fileSystem = $fileSystem;
		$this->imageManager = $imageManager;
	}

	public function createHandler(string $file_type): AbstractMediaHandler
	{
		switch ($file_type)
		{
			case 'video':
				return new Video($this->config, $this->fileSystem);
			case 'pdf':
				return new Pdf($this->config, $this->fileSystem);
			case 'image':
				return new Image($this->config, $this->fileSystem, $this->imageManager);
			default:
				throw new \InvalidArgumentException('Unknown file type');
		}
	}


}
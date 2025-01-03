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
use App\Framework\Exceptions\CoreException;
use InvalidArgumentException;
use League\Flysystem\Filesystem;

class MediaHandlerFactory
{
	private Config $config;
	private Filesystem $fileSystem;
	private ImagickFactory $imagickFactory;

	/**
	 * @param Config         $config
	 * @param Filesystem     $fileSystem
	 * @param ImagickFactory $imagickFactory
	 */
	public function __construct(Config $config, Filesystem $fileSystem,	ImagickFactory $imagickFactory)
	{
		$this->config = $config;
		$this->fileSystem = $fileSystem;
		$this->imagickFactory = $imagickFactory;
	}

	/**
	 * @throws CoreException
	 */
	public function createHandler(string $mimeType): AbstractMediaHandler
	{
		return match (true)
		{
			str_starts_with($mimeType, 'image/') => new Image($this->config, $this->fileSystem, $this->imagickFactory->createImagick()),
			str_starts_with($mimeType, 'video/') => new Video($this->config, $this->fileSystem, $this->imagickFactory->createImagick()),
			$mimeType === 'application/pdf' =>  new Pdf($this->config, $this->fileSystem, $this->imagickFactory->createImagick()),
			default => throw new InvalidArgumentException('Unknown file type'),
		};

	}


}
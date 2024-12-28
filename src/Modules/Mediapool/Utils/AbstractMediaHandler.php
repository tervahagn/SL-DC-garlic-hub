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
use League\Flysystem\Filesystem;

abstract class AbstractMediaHandler
{
	protected Config $config;
	protected Filesystem $filesystem;

	/**
	 * @param Config     $config
	 * @param Filesystem $filesystem
	 */
	public function __construct(Config $config, Filesystem $filesystem)
	{
		$this->config = $config;
		$this->filesystem = $filesystem;
	}


}
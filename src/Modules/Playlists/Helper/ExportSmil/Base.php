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

namespace App\Modules\Playlists\Helper\ExportSmil;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\ModuleException;

/**
 * Export playlist items from db and write the SMIL-file body to disc
 * Channels and internal playlists are assigned as placeholders like {XYZ_ITEM_2}
 * It also creates a SMIL-file for the Javascript SMIL preview-player
 *
 * Class player_playlist creates at least complete SMIL head and body from the
 * files created by this class
 *
 * Real playlist duration and filesize needs be calculated after export and set to database
 */
abstract class Base
{
	protected Config $config;
	protected string $playlist_base_path;
	protected string $media_pool_path;
	protected string $templates_path;

	public function  __construct(Config $config)
	{
		$this->config = $config;
	}

	abstract public function createMediaSymlinks(Content $Content);
	abstract public function createTemplatesSymlinks(Content $Content);


	public function setPlaylistBasePath($path): static
	{
		$real_path = realpath(_BasePath . $path);
		if ($real_path === false)
			throw new ModuleException($this->moduleName, 'Playlist path of ' . $real_path . ' does not exists');

		$this->playlist_base_path =  $real_path . DIRECTORY_SEPARATOR;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPlaylistBasePath()
	{
		return $this->playlist_base_path;
	}

	/**
	 * @param   string  $path
	 * @return  $this
	 * @throws  ModuleException
	 */
	public function setMediaPoolPath($path)
	{
		$this->media_pool_path = Base . phprealpath($path) . DIRECTORY_SEPARATOR;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMediaPoolPath()
	{
		return $this->media_pool_path;
	}

	/**
	 * @param   string  $path
	 * @return  $this
	 * @throws  ModuleException
	 */
	public function setTemplatesPath($path)
	{
		$this->templates_path = Base . phprealpath($path) . DIRECTORY_SEPARATOR;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTemplatesPath()
	{
		return $this->templates_path;
	}

// ======================  protected Functions ============================================

// ==================== Service methods ======================================

}
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


namespace App\Modules\Player\IndexCreation;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;

class IndexFileHandler
{
	private readonly Config $config;
	private readonly string $systemPath;
	private string $filePath;

	public function __construct(Config $config, string $systemPath)
	{
		$this->config = $config;
		$this->systemPath = $systemPath;
	}

	/**
	 * @throws CoreException
	 */
	public function handleForbidden(): void
	{
		$this->filePath = $this->config->getConfigValue('defaults', 'player', 'SmilDirectories').'/forbidden.smil';
	}


	public function handleNew($ownerId): void
	{
		// ToDo Transferhandler
		// insert new player
	}

	public function handleUnreleased(): void
	{
		// Todo Transferhandler
		// send transfer code smil or unreleased
	}

	public function handleReleased(): void
	{
		// generate Index from Playlist
	}

	/**
	 * @throws CoreException
	 */
	public function handleTestSMil(): void
	{
		$this->filePath = $this->config->getConfigValue('tests', 'player', 'SmilDirectories').'/index.smil';
	}

	/**
	 * @throws CoreException
	 */
	public function handleCorrectSMil(): void
	{
		$this->filePath = $this->config->getConfigValue('simulations', 'player', 'SmilDirectories').'/without_errors.smil';
	}

	/**
	 * @throws CoreException
	 */
	public function handleCorruptSMIL(): void
	{
		$this->filePath = $this->config->getConfigValue('simulations', 'player', 'SmilDirectories').'/broken_index.smil';
	}

	/**
	 * @throws CoreException
	 */
	public function handleCorruptContent(): void
	{
		$this->filePath = $this->config->getConfigValue('simulations', 'player', 'SmilDirectories').'/unreachable_content.smil';
	}

	/**
	 * @throws CoreException
	 */
	public function handleCorruptPrefetchContent(): void
	{
		$this->filePath = $this->config->getConfigValue('simulations', 'player', 'SmilDirectories').'/unreachable_prefetch_content.smil';
	}

	public function getFilePath(): string
	{
		return $this->systemPath.'/'.$this->filePath;
	}
}
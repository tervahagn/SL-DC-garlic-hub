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

namespace App\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

readonly class Facade
{
	private Builder $settingsFormBuilder;
	private PlaylistsService $playlistsService;
	private Parameters $settingsParameters;
	private TemplateRenderer $renderer;

	public function __construct(Builder $settingsFormBuilder, PlaylistsService $playlistsService, Parameters $settingsParameters, TemplateRenderer $renderer)
	{
		$this->settingsFormBuilder = $settingsFormBuilder;
		$this->playlistsService = $playlistsService;
		$this->settingsParameters = $settingsParameters;
		$this->renderer = $renderer;
	}

	/**
	 * @throws Exception
	 */
	public function loadPlaylistForEdit($playlistId): array
	{
		return $this->playlistsService->loadPlaylistForEdit($playlistId);
	}

	public function init(Translator $translator, Session $session): void
	{
		$this->settingsFormBuilder->init($session);
		$this->playlistsService->setUID($session->get('user')['UID']);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function configurePlaylistFormParameter(array $post): array
	{
		if (isset($post['playlist_id']) && $post['playlist_id'] > 0)
		{
			$playlist = $this->playlistsService->loadPlaylistForEdit($post['playlist_id']);
			$this->settingsFormBuilder->configEditParameter($playlist);
		}
		else
		{
			$this->settingsFormBuilder->configNewParameter($post['playlist_mode']);
		}

		return $this->settingsFormBuilder->handleUserInput($post);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function storePlaylist($post): int
	{
		$saveData  = array_combine(
			$this->settingsParameters->getInputParametersKeys(),
			$this->settingsParameters->getInputValuesArray()
		);
		if (isset($post['playlist_id']) && $post['playlist_id'] > 0)
			$id = $this->playlistsService->update($saveData);
		else
			$id = $this->playlistsService->createNew($saveData);

		return $id;
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function buildCreateNewParameter(string $playlistMode): void
	{
		$this->settingsFormBuilder->configNewParameter($playlistMode);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function buildEditParameter(array $playlist): void
	{
		$this->settingsFormBuilder->configEditParameter($playlist);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function render($post): array
	{
		$elements = $this->settingsFormBuilder->buildForm($post);

		return $this->renderer->renderTemplate($elements, $post['playlist_mode']);
	}

}
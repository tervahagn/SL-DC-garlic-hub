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

namespace App\Modules\Playlists\Controller;

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Helper\Settings\Facade;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class ShowSettingsController
{
	private readonly Facade $facade;
	private Messages $flash;

	public function __construct(Facade $facade)
	{
		$this->facade              = $facade;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws Exception
	 * @throws ModuleException
	 */
	public function newPlaylistForm(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlist = ['playlist_mode' => $args['playlist_mode'] ?? 'master'];

		$this->initFacade($request);
		$this->facade->buildCreateNewParameter($playlist['playlist_mode']);

		return $this->outputRenderedForm($response, $playlist);
	}


	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function editPlaylistForm(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;

		$this->initFacade($request);

		if ($playlistId === 0)
		{
			$this->flash->addMessage('error', 'Playlist ID not valid.');
			return $response->withHeader('Location', '/playlists')->withStatus(302);
		}

		$playlist = $this->facade->loadPlaylistForEdit($playlistId);
		if (empty($playlist))
		{
			$this->flash->addMessage('error', 'Playlist not found.');
			return $response->withHeader('Location', '/playlists')->withStatus(302);
		}
		$this->facade->buildEditParameter($playlist);

		return $this->outputRenderedForm($response, $playlist);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws Exception
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$post        = $request->getParsedBody();

		$this->initFacade($request);
		$errors = $this->facade->configurePlaylistFormParameter($post);
		foreach ($errors as $errorText)
		{
			$this->flash->addMessageNow('error', $errorText);
		}

		if (!empty($errors))
			return $this->outputRenderedForm($response, $post);

		$id = $this->facade->storePlaylist($post);
		if ($id > 0)
		{
			$this->flash->addMessage('success', 'Playlist “'.$post['playlist_name'].'“ successfully stored.');
			return $response->withHeader('Location', '/playlists')->withStatus(302);
		}

		return $this->outputRenderedForm($response, $post);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	private function outputRenderedForm(ResponseInterface $response, array $userInput): ResponseInterface
	{
		$data = $this->facade->render($userInput);
		$response->getBody()->write(serialize($data));
		return $response->withHeader('Content-Type', 'text/html');
	}

	private function initFacade(ServerRequestInterface $request): void
	{
		$this->flash      = $request->getAttribute('flash');
		$this->facade->init($request->getAttribute('session'));
	}
}
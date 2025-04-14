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

use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Helper\Datatable\Parameters;
use App\Modules\Playlists\Services\PlaylistsDatatableService;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlaylistsController
{
	private readonly PlaylistsService $playlistsService;
	private readonly PlaylistsDatatableService $playlistsDatatableService;
	private readonly Parameters $parameters;
	private Session $session;

	/**
	 * @param PlaylistsService $playlistsService
	 * @param PlaylistsDatatableService $playlistsDatatableService
	 * @param Parameters $parameters
	 */
	public function __construct(PlaylistsService $playlistsService, PlaylistsDatatableService $playlistsDatatableService, Parameters $parameters)
	{
		$this->playlistsService          = $playlistsService;
		$this->playlistsDatatableService = $playlistsDatatableService;
		$this->parameters                = $parameters;
	}


	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$post        = $request->getParsedBody();
		$playlistId = (int) $post['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->session = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);

		if ($this->playlistsService->delete($playlistId) === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist not found.']);

		return $this->jsonResponse($response, ['success' => true]);
	}

	/*
	public function shuffle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		// Todo:

		return $this->jsonResponse($response, ['success' => true]);
	}

	public function picking(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		// Todo:

		return $this->jsonResponse($response, ['success' => true]);
	}
*/
	public function loadZone(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->session    = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);

		$multizone = $this->playlistsService->loadPlaylistForMultizone($playlistId);
		if ($this->playlistsService->hasErrorMessages())
			return $this->jsonResponse($response, ['success' => false, 'error_message' => $this->playlistsService->getErrorMessages()]);

		return $this->jsonResponse($response, ['success' => true, 'zones' => $multizone]);
	}

	public function saveZone(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->session = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);
		$count = $this->playlistsService->saveZones($playlistId, $request->getParsedBody());
		if ($count === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Multizone could not be saved']);


		return $this->jsonResponse($response, ['success' => true]);
	}

	/**
	 * @throws CoreException
	 * @throws Exception
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function findByName(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$this->parameters->setUserInputs($args);
		$this->parameters->parseInputAllParameters();

		$this->session = $request->getAttribute('session');
		$this->playlistsDatatableService->setUID($this->session->get('user')['UID']);
		$this->playlistsDatatableService->loadDatatable();
		$results = [];
		foreach ($this->playlistsDatatableService->getCurrentFilterResults() as $value)
		{
			$results[] = ['id' => $value['playlist_id'], 'name' => $value['playlist_name']];
		}

		return $this->jsonResponse($response, $results);

	}

	public function findById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->session = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);

		$result = $this->playlistsService->loadNameById($playlistId);
		return $this->jsonResponse($response, $result);

	}

	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

}
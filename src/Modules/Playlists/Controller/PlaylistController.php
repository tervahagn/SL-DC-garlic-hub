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
use App\Modules\Playlists\Services\PlaylistsEditService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlaylistController
{
	private readonly PlaylistsEditService $playlistsService;
	private Session $session;

	/**
	 * @param PlaylistsEditService $playlistsService
	 */
	public function __construct(PlaylistsEditService $playlistsService)
	{
		$this->playlistsService = $playlistsService;
	}

	public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		if ($this->playlistsService->delete($playlistId) === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist not found.']);

		return $this->jsonResponse($response, ['success' => true]);
	}

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

	public function loadZone(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->session    = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);

		$multizone = $this->playlistsService->loadPlaylistForMultizone($playlistId);
		if ( $this->playlistsService->hasErrorMessages())
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


	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

}
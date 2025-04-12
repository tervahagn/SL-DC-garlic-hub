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
use App\Modules\Playlists\Services\ItemsService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ItemsController
{
	private readonly ItemsService $itemsService;

	public function __construct(ItemsService $itemsService)
	{
		$this->itemsService = $itemsService;
	}

	/**
	 * @throws Exception
	 */
	public function loadItems(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->determineUID($request->getAttribute('session'));
		$list = $this->itemsService->loadItemsByPlaylistId($playlistId);

		return $this->jsonResponse($response, ['success' => true, 'data' => $list]);
	}


	/**
	 * @throws Exception
	 */
	public function insert(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$requestData = $request->getParsedBody();
		if (empty($requestData['playlist_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		if (empty($requestData['id'])) // more performant as isset and check for 0 or ''
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Content ID not valid.']);

		if (empty($requestData['source']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Source not valid.']);

		if (empty($requestData['position']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Position not valid.']);

		$this->determineUID($request->getAttribute('session'));

		switch ($requestData['source'])
		{
			case 'media':
			case 'mediapool':
				$item = $this->itemsService->insertMedia((int)$requestData['playlist_id'], $requestData['id'], $requestData['position']);
				break;
			case 'playlist':
				$item = $this->itemsService->insertPlaylist((int)$requestData['playlist_id'], $requestData['id'], $requestData['position']);
				break;
			default:
				$item = [];
		}

		if(!empty($item))
			return $this->jsonResponse($response, ['success' => true, 'data' => $item]);
		else
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Error inserting item.']);
	}

	public function updateItemOrders(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$requestData = $request->getParsedBody();
		if (empty($requestData['playlist_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		if (empty($requestData['items_positions']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Items Position array is not valid.']);

		$this->itemsService->setUID($request->getAttribute('session')->get('user')['UID']);
		$this->itemsService->updateItemOrder($requestData['playlist_id'], $requestData['items_positions']);

		return $this->jsonResponse($response, ['success' => true]);
	}

	public function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$requestData = $request->getParsedBody();
		if (empty($requestData['playlist_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		if (empty($requestData['item_id'])) // more performant as isset and check for 0 or ''
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Item ID not valid.']);

		$this->determineUID($request->getAttribute('session'));

		$item = $this->itemsService->delete((int)$requestData['playlist_id'], (int) $requestData['item_id']);

		if(!empty($item))
			return $this->jsonResponse($response, ['success' => true, 'data' => $item]);
		else
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Error deleting item.']);
	}


	private function determineUID(Session $session)
	{
		$this->itemsService->setUID($session->get('user')['UID']);
	}

	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}
}
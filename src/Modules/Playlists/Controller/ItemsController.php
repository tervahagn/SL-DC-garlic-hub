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
use App\Modules\Playlists\Services\InsertItems\InsertItemFactory;
use App\Modules\Playlists\Services\ItemsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ItemsController
{
	private readonly ItemsService $itemsService;
	private readonly InsertItemFactory $insertItemFactory;

	public function __construct(ItemsService $itemsService, InsertItemFactory $insertItemFactory)
	{
		$this->itemsService = $itemsService;
		$this->insertItemFactory = $insertItemFactory;
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
		$list = $this->itemsService->loadItemsByPlaylistIdForComposer($playlistId);

		return $this->jsonResponse($response, ['success' => true, 'data' => $list]);
	}

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


		$insertItem = $this->insertItemFactory->create($requestData['source']);
		if($insertItem === null)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Error inserting item.']);

		$insertItem->setUID($request->getAttribute('session')->get('user')['UID']);
		$item = $insertItem->insert($requestData['playlist_id'], $requestData['id'], $requestData['position']);

		return $this->jsonResponse($response, ['success' => true, 'data' => $item]);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function edit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$requestData = $request->getParsedBody();
		if (empty($requestData['item_id'])) // more performant as isset and check for 0 or ''
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Item ID not valid.']);

		if (empty($requestData['name']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No parameter name.']);

		if (empty($requestData['value']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No parameter value.']);

		$this->itemsService->setUID($request->getAttribute('session')->get('user')['UID']);

		switch ($requestData['name'])
		{
			case 'item_name':
				$affected = $this->itemsService->updateField($requestData['item_id'], $requestData['name'], $requestData['value']);
				break;
			default:
				return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No valid parametername.']);
		}

		if ($affected === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Error updating item field: '.$requestData['name']. '.']);

		return $this->jsonResponse($response, ['success' => true]);
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
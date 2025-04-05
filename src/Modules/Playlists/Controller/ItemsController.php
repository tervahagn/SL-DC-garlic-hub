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

use App\Modules\Mediapool\Services\MediaService;
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
	public function insert(ServerRequestInterface $request, ResponseInterface $response)
	{
		$requestData = $request->getParsedBody();
		if (!isset($requestData['playlist_id']) && $requestData['playlist_id'] === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		if (empty($requestData['id'])) // more performant as isset and check for 0 or ''
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Content ID not valid.']);

		if (!isset($requestData['source']) && $requestData['source'] === '')
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Source not valid.']);

		$session = $request->getAttribute('session');
		$this->itemsService->setUID($session->get('user')['UID']);

		$id = $this->itemsService->insert((int)$requestData['playlist_id'], $requestData['id'], $requestData['source']);

		if($id > 0)
			$this->jsonResponse($response, ['success' => true]);
		else
			$this->jsonResponse($response, ['success' => false, 'error_message' => 'Error inserting item.']);
	}

	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}
}
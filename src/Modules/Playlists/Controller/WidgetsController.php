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

use App\Modules\Playlists\Services\ItemsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WidgetsController
{
	private ItemsService $itemsService;

	public function __construct(ItemsService $itemsService)
	{
		$this->itemsService = $itemsService;
	}


	public function fetch(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$itemId = $args['item_id'] ?? 0;
		if ($itemId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Item ID not valid.']);

		$this->itemsService->setUID($request->getAttribute('session')->get('user')['UID']);
		$item = $this->itemsService->fetchItemById($itemId);
		if ($item['mimetype'] !== 'application/widget')
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Not a widget item.']);

		return $this->jsonResponse($response, ['success' => true, 'data' => $item]);

	}

	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

}
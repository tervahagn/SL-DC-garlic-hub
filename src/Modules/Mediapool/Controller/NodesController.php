<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Modules\Mediapool\Controller;

use App\Modules\Mediapool\NodesService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NodesController
{
	private NodesService $nodesService;

	/**
	 * @param NodesService $nodesService
	 */
	public function __construct(NodesService $nodesService)
	{
		$this->nodesService = $nodesService;
	}

	/**
	 * @throws Exception
	 */
	public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$parent_id = (array_key_exists('parent_id', $args)) ? (int) $args['parent_id'] : 0;
		$result = $this->nodesService->getNodes($parent_id);

		$payload = json_encode($result);
		$response->getBody()->write($payload);
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(200);
	}

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(200);
	}

	public function edit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(200);
	}

	public function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		return $response
			->withHeader('Content-Type', 'application/json')
			->withStatus(200);
	}


}
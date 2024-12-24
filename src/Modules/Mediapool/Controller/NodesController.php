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

use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\NodesService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;

class NodesController
{
	private NodesService $nodesService;
	private int $UID;

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
		if (!$this->hasRights($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}

		$parent_id = (array_key_exists('parent_id', $args)) ? (int) $args['parent_id'] : 0;
		$result = $this->nodesService->getNodes($parent_id);

		$payload = json_encode($result);
		$response->getBody()->write($payload);

		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}


	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		if (!$this->hasRights($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}
		$contentType = $request->getHeaderLine('Content-Type');
		$rawBody = (string) $request->getBody();

		$queryParams = $request->getParsedBody();
		if (!array_key_exists('name', $queryParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node name is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		try
		{
			$parent_id = 0;
			if (!array_key_exists('parent_id', $queryParams))
				$parent_id = (int) $queryParams['parent_id'];

			$this->nodesService->setUID($this->UID);
			$node_id = $this->nodesService->addNode($parent_id, $queryParams['name']);
			$response->getBody()->write(json_encode(['success' => true, 'data' => ['node_id' => $node_id]]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
		catch (Exception | FrameworkException | ModuleException $e)
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => $e->getMessage()]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
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

	private function hasRights(Helper $session): bool
	{
		$ret = $session->exists('user');
		if ($ret)
			$this->UID = $session->get('user')['UID'];

		return $ret;
	}

}
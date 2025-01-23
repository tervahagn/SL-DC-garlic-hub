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

use App\Framework\Core\Session;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Mediapool\Services\NodesService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
		if (!$this->isLogged($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}

		$parent_id = (array_key_exists('parent_id', $args)) ? (int) $args['parent_id'] : 0;
		$this->nodesService->setUID($this->UID);
		$result = $this->nodesService->getNodes($parent_id);

		$payload = json_encode($result);
		$response->getBody()->write($payload);

		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		if (!$this->isLogged($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}

		$bodyParams = $request->getParsedBody();
		if (!array_key_exists('name', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node name is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		try
		{
			$node_id = 0;
			if (array_key_exists('node_id', $bodyParams))
				$node_id = (int) $bodyParams['node_id'];

			$this->nodesService->setUID($this->UID);
			$new_node_id = $this->nodesService->addNode($node_id, $bodyParams['name']);

			$response->getBody()->write(json_encode([
				'success' => true,
				'data' => ['id' => $new_node_id, 'new_name' => $bodyParams['name']]
				])
			);
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
		catch (Exception | ModuleException $e)
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => $e->getMessage()]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
	}

	public function edit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		if (!$this->isLogged($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}

		$bodyParams = $request->getParsedBody();
		if (!array_key_exists('name', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node name is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		if (!array_key_exists('node_id', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		$visibility = null;
		if (array_key_exists('visibility', $bodyParams))
		{
			$visibility = $bodyParams['visibility'];
		}

		try
		{
			$this->nodesService->setUID($this->UID);
			$count = $this->nodesService->editNode($bodyParams['node_id'], $bodyParams['name'], $visibility);
			if ($count === 0)
				throw new ModuleException('mediapool', 'Edit node failed');

			$response->getBody()->write(json_encode([
					'success' => true,
					'data' => [
						'id' => $bodyParams['node_id'],
						'new_name' => $bodyParams['name'],
						'visibility' => $visibility
					]
				])
			);
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
		catch (CoreException | PhpfastcacheSimpleCacheException | Exception | ModuleException $e)
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => $e->getMessage()]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
	}

	public function move(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		if (!$this->isLogged($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}

		$bodyParams = $request->getParsedBody();
		if (!array_key_exists('src_node_id', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'Source node is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		if (!array_key_exists('target_node_id', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'Target node is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		if (!array_key_exists('target_region', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'Target region is 
			missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		try
		{
			$this->nodesService->setUID($this->UID);
			$count = $this->nodesService->moveNode(
				$bodyParams['src_node_id'],
				$bodyParams['target_node_id'],
				$bodyParams['target_region']
			);
			$response->getBody()->write(json_encode(['success' => true, 'data' => ['count_deleted_nodes' => $count]]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
		catch (Exception | FrameworkException | ModuleException $e)
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => $e->getMessage()]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

	}


	public function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		if (!$this->isLogged($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}

		$bodyParams = $request->getParsedBody();
		if (!array_key_exists('node_id', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		try
		{
			$this->nodesService->setUID($this->UID);
			$count = $this->nodesService->deleteNode($bodyParams['node_id']);
			$response->getBody()->write(json_encode(['success' => true, 'data' => ['count_deleted_nodes' => $count]]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
		catch (Exception | FrameworkException | ModuleException $e)
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => $e->getMessage()]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

	}

	private function isLogged(Session $session): bool
	{
		$ret = $session->exists('user');
		if ($ret)
			$this->UID = $session->get('user')['UID'];

		return $ret;
	}

}
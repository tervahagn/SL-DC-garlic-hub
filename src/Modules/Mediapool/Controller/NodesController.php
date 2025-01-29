<?php

namespace App\Modules\Mediapool\Controller;

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\DatabaseException;
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

	public function __construct(NodesService $nodesService)
	{
		$this->nodesService = $nodesService;
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$parent_id = $args['parent_id'] ?? 0;
		$this->nodesService->setUID($request->getAttribute('session')->get('user')['UID']);
		$result = $this->nodesService->getNodes($parent_id);

		$response->getBody()->write(json_encode($result));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

	/**
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws DatabaseException
	 * @throws CoreException
	 */
	public function add(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!isset($bodyParams['name']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'node name is missing']);

		try
		{
			$node_id = $bodyParams['node_id'] ?? 0;
			$this->nodesService->setUID($request->getAttribute('session')->get('user')['UID']);
			$new_node_id = $this->nodesService->addNode($node_id, $bodyParams['name']);

			return $this->jsonResponse($response, [
				'success' => true,
				'data' => ['id' => $new_node_id, 'new_name' => $bodyParams['name']]
			]);
		}
		catch (Exception | ModuleException $e)
		{
			return $this->jsonResponse($response, ['success' => false, 'error_message' => $e->getMessage()]);
		}
	}

	public function edit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!isset($bodyParams['name']) || !isset($bodyParams['node_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'node name or id is missing']);

		$visibility = $bodyParams['visibility'] ?? null;

		try
		{
			$this->nodesService->setUID($request->getAttribute('session')->get('user')['UID']);
			$count = $this->nodesService->editNode($bodyParams['node_id'], $bodyParams['name'], $visibility);
			if ($count === 0)
				throw new ModuleException('mediapool', 'Edit node failed');

			return $this->jsonResponse($response, [
				'success' => true,
				'data' => [
					'id' => $bodyParams['node_id'],
					'new_name' => $bodyParams['name'],
					'visibility' => $visibility
				]
			]);
		}
		catch (CoreException | PhpfastcacheSimpleCacheException | Exception | ModuleException $e)
		{
			return $this->jsonResponse($response, ['success' => false, 'error_message' => $e->getMessage()]);
		}
	}

	/**
	 * @throws DatabaseException
	 */
	public function move(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!isset($bodyParams['src_node_id']) || !isset($bodyParams['target_node_id']) || !isset($bodyParams['target_region']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Source node, target node, or target region is missing']);

		try
		{
			$this->nodesService->setUID($request->getAttribute('session')->get('user')['UID']);
			$count = $this->nodesService->moveNode($bodyParams['src_node_id'], $bodyParams['target_node_id'], $bodyParams['target_region']);
			return $this->jsonResponse($response, ['success' => true, 'data' => ['count_deleted_nodes' => $count]]);
		}
		catch (Exception | FrameworkException | ModuleException $e)
		{
			return $this->jsonResponse($response, ['success' => false, 'error_message' => $e->getMessage()]);
		}
	}

	/**
	 * @throws CoreException
	 * @throws DatabaseException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!isset($bodyParams['node_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'node is missing']);

		try
		{
			$this->nodesService->setUID($request->getAttribute('session')->get('user')['UID']);
			$count = $this->nodesService->deleteNode($bodyParams['node_id']);
			return $this->jsonResponse($response, ['success' => true, 'data' => ['count_deleted_nodes' => $count]]);
		}
		catch (Exception | FrameworkException | ModuleException $e)
		{
			return $this->jsonResponse($response, ['success' => false, 'error_message' => $e->getMessage()]);
		}
	}

	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}
}
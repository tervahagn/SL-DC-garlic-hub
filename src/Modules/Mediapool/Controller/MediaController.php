<?php

namespace App\Modules\Mediapool\Controller;

use App\Modules\Mediapool\Services\MediaService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MediaController
{
	private MediaService $mediaService;

	public function __construct(MediaService $mediaService)
	{
		$this->mediaService = $mediaService;
	}

	/**
	 * @throws Exception
	 */
	public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$node_id = $args['node_id'] ?? 0;
		if ($node_id === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'node is missing']);

		$media_list = $this->mediaService->listMedia($node_id);
		return $this->jsonResponse($response, ['success' => true, 'media_list' => $media_list]);
	}

	public function getInfo(ServerRequestInterface $request, ResponseInterface $response, array $args):
	ResponseInterface
	{
		$media_id = $args['media_id'] ?? 0;
		if ($media_id === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'mwdia_id is missing']);

		$media = $this->mediaService->getMedia($media_id);
		return $this->jsonResponse($response, ['success' => true, 'media' => $media]);
	}

	public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!array_key_exists('media_id', $bodyParams))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'media id is missing']);

		if (!array_key_exists('filename', $bodyParams))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Filename is missing']);

		if (!array_key_exists('description', $bodyParams))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Description is missing']);

		$this->mediaService->updateMedia($bodyParams['media_id'], $bodyParams['filename'], $bodyParams['description']);
		return $this->jsonResponse($response, ['success' => true]);
	}


	/**
	 * @throws Exception
	 */
	public function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!isset($bodyParams['media_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'media id is missing']);

		$this->mediaService->setUID($request->getAttribute('session')->get('user')['UID']);
		$count = $this->mediaService->deleteMedia($bodyParams['media_id']);
		return $this->jsonResponse($response, ['success' => true, 'data' => ['deleted_media' => $count]]);
	}

	/**
	 * @throws Exception
	 */
	public function move(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!isset($bodyParams['media_id']) || !isset($bodyParams['node_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'media id or node is missing']);

		$this->mediaService->setUID($request->getAttribute('session')->get('user')['UID']);
		$count = $this->mediaService->moveMedia($bodyParams['media_id'], $bodyParams['node_id']);
		return $this->jsonResponse($response, ['success' => true, 'data' => ['deleted_media' => $count]]);
	}

	/**
	 * @throws Exception
	 */
	public function clone(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!isset($bodyParams['media_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'media id is missing']);

		$this->mediaService->setUID($request->getAttribute('session')->get('user')['UID']);
		$new_media = $this->mediaService->cloneMedia($bodyParams['media_id']);
		return $this->jsonResponse($response, ['success' => true, 'new_media' => $new_media]);
	}

	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

}
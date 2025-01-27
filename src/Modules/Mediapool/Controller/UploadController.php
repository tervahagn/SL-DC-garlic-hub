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
use App\Modules\Mediapool\Services\UploadService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UploadController
{
	private UploadService $uploadService;
	private int $UID = 0;

	public function __construct(UploadService $uploadService)
	{
		$this->uploadService = $uploadService;
	}

	public function requestApi(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		if (!$this->hasRights($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}

		$bodyParams = $request->getParsedBody();
		if (!array_key_exists('api_url', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'api_url missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		$headers = null;
		if (array_key_exists('headers', $bodyParams))
			$headers = $bodyParams['headers'];

		$body = $this->uploadService->requestApi($bodyParams['api_url'], $headers);
		$response->getBody()->write(json_encode(['success' => true, 'data' => $body],JSON_UNESCAPED_UNICODE));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

	/**
	 * @throws Exception
	 */
	public function upload(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		if (!$this->hasRights($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}
		$uploadedFile = $request->getUploadedFiles();
		if (empty($uploadedFile))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'No files to upload.']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		$bodyParams = $request->getParsedBody();

		if (!array_key_exists('node_id', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}
		$node_id = (int) $bodyParams['node_id'];

		if ($node_id === 0)
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		if (empty($uploadedFile['file']))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'no files']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		$metadata = [];
		if (array_key_exists('metadata', $bodyParams))
		{
			$metadata = json_decode($bodyParams['metadata'], true) ?? [];
		}
		$succeed = $this->uploadService->uploadMediaFiles($node_id, $this->UID, $uploadedFile['file'], $metadata);

		$response->getBody()->write(json_encode($succeed));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}

	public function uploadExternal(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		if (!$this->hasRights($request->getAttribute('session')))
		{
			$response->getBody()->write(json_encode([]));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
		}

		$bodyParams    = $request->getParsedBody();

		if (!array_key_exists('node_id', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		$node_id = (int) $bodyParams['node_id'];
		if ($node_id === 0)
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'node is missing']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		if (!array_key_exists('external_link', $bodyParams))
		{
			$response->getBody()->write(json_encode(['success' => false, 'error_message' => 'no files']));
			return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
		}

		$succeed = $this->uploadService->uploadExternalMedia($node_id, $this->UID, $bodyParams['external_link']);

		$response->getBody()->write(json_encode($succeed, JSON_UNESCAPED_UNICODE));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}


	private function hasRights(Session $session): bool
	{
		$ret = $session->exists('user');
		if ($ret)
			$this->UID = $session->get('user')['UID'];

		return $ret;
	}
}
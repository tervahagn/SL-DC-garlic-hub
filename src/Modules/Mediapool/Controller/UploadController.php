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

namespace App\Modules\Mediapool\Controller;

use App\Modules\Mediapool\Services\UploadService;
use Doctrine\DBAL\Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UploadController
{
	private UploadService $uploadService;

	public function __construct(UploadService $uploadService)
	{
		$this->uploadService = $uploadService;
	}

	/**
	 * @throws GuzzleException
	 */
	public function searchStockImages(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!is_array($bodyParams))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No body parameter.']);

		if (!isset($bodyParams['api_url']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'api_url missing']);

		$headers = $bodyParams['headers'] ?? null;
		$body    = $this->uploadService->requestApi($bodyParams['api_url'], $headers);
		return $this->jsonResponse($response, ['success' => true, 'data' => $body]);
	}

	/**
	 * @throws Exception
	 */
	public function uploadLocalFile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$uploadedFile = $request->getUploadedFiles();
		if (empty($uploadedFile))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No files to upload.']);

		$bodyParams = $request->getParsedBody();
		if (!is_array($bodyParams))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No files parameter.']);

		$node_id    = (int)($bodyParams['node_id'] ?? 0);
		if ($node_id === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'node is missing']);

		if (empty($uploadedFile['file']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'no files']);

		$metadata = json_decode($bodyParams['metadata'] ?? '[]', true) ?? [];

		$UID      = $request->getAttribute('session')->get('user')['UID'];
		$succeed  = $this->uploadService->uploadMediaFiles($node_id, $UID, $uploadedFile['file'], $metadata);

		return $this->jsonResponse($response, $succeed[0]);
	}

	public function uploadFromUrl(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		if (!is_array($bodyParams))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No files to upload.']);

		$node_id  = $bodyParams['node_id'] ?? 0;
		if ($node_id === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'node is missing']);

		if (!isset($bodyParams['external_link']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'No external link submitted.']);

		$metadata = [];
		if (isset($bodyParams['metadata']))
			$metadata = json_decode($bodyParams['metadata'], true);

		$UID     = $request->getAttribute('session')->get('user')['UID'];
		$succeed = $this->uploadService->uploadExternalMedia($node_id, $UID, $bodyParams['external_link'], $metadata);

		return $this->jsonResponse($response, $succeed);
	}

	/**
	 * @param array<string,mixed> $data
	 */
	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$json = json_encode($data, JSON_UNESCAPED_UNICODE);
		if ($json !== false)
			$response->getBody()->write($json);
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}
}
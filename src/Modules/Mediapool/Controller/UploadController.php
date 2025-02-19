<?php

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
		$node_id    = (int)($bodyParams['node_id'] ?? 0);
		if ($node_id === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'node is missing']);

		if (empty($uploadedFile['file']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'no files']);

		$metadata = json_decode($bodyParams['metadata'] ?? '[]', true) ?? [];
		$UID      = $request->getAttribute('session')->get('user')['UID'];
		$succeed  = $this->uploadService->uploadMediaFiles($node_id, $UID, $uploadedFile['file'], $metadata);

		return $this->jsonResponse($response, $succeed);
	}

	public function uploadFromUrl(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$bodyParams = $request->getParsedBody();
		$node_id    = (int)($bodyParams['node_id'] ?? 0);
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

	private function jsonResponse(ResponseInterface $response, array $data): ResponseInterface
	{
		$response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}
}
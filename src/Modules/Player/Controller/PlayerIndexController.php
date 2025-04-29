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


namespace App\Modules\Player\Controller;

use App\Framework\Core\Sanitizer;
use App\Modules\Player\Services\PlayerIndexService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Stream;

class PlayerIndexController
{
	private readonly PlayerIndexService $indexService;
	private readonly Sanitizer $sanitizer;

	public function __construct(PlayerIndexService $indexService, Sanitizer $sanitizer)
	{
		$this->indexService  = $indexService;
		$this->sanitizer     = $sanitizer;
	}


	public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$ownerId    = $this->sanitizer->int($_GET['owner_id'] ?? 0);
		$server     = $request->getServerParams();
		$userAgent  = $server['HTTP_USER_AGENT'];
		$serverName = $server['SERVER_NAME'];

	//	$userAgent = 'GAPI/1.0 (UUID:dbfbe32a-7bec-4e1d-a5cd-297228ae3ef4; NAME:Arch Greece) garlic-linux/v0.6.0.679 (MODEL:Garlic)';
		$this->indexService->setUID($ownerId);
		if (str_contains($serverName, 'localhost') || str_contains($serverName, 'ddev'))
			$localPlayer = true;
		else
			$localPlayer = false;

		$filePath = $this->indexService->handleIndexRequest($userAgent, $localPlayer);
		if (empty($filePath))
			return $response->withHeader('Content-Type', 'application/smil+xml')->withStatus(404);

		return $this->sendSmilHeader($response, $server, $filePath);
	}

	private function sendSmilHeader(ResponseInterface $response, array $server, string $filePath): ResponseInterface
	{
		$lastModified = gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT';
		$cacheControl = 'public, must-revalidate, max-age=864000, pre-check=864000';

		$modifiedSince = $server['HTTP_IF_MODIFIED_SINCE'] ?? $server['If-Modified-Since'] ?? null;
		if ($modifiedSince !== null && strtotime($modifiedSince) <= filemtime($filePath)) {
			return $response
				->withHeader('Cache-Control', $cacheControl)
				->withHeader('Last-Modified', $lastModified)
				->withStatus(304);
		}

		if ($server['REQUEST_METHOD'] === 'HEAD')
		{
			return $response
				->withHeader('Cache-Control', $cacheControl)
				->withHeader('Content-Type', 'application/smil+xml')
				->withHeader('Last-Modified', $lastModified)
				->withStatus(200);
		}
		else
		{
			// not cached or cache outdated, 200 OK send index.smil
			$fileStream = new Stream(fopen($filePath, 'rb'));
			return $response
				->withBody($fileStream)
				->withHeader('Cache-Control', $cacheControl)
				->withHeader('Last-Modified', $lastModified)
				->withHeader('Content-Length', (string)filesize($filePath)) // will not work with php-fpm or nginx
				->withHeader('Content-Type', 'application/smil+xml')
				->withHeader('Content-Description', 'File Transfer')
				->withHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"')
				->withStatus(200);

		}
	}
}
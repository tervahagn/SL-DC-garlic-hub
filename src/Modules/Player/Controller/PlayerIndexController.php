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
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Services\PlayerIndexService;
use Doctrine\DBAL\Exception;
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

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws Exception
	 */
	public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$ownerId    = $this->sanitizer->int($_GET['owner_id'] ?? 0);
		$server     = $request->getServerParams();
		$userAgent  = $server['HTTP_USER_AGENT'];
		$serverName = $server['SERVER_NAME'];

		$userAgent = 'SCAPI/1.0 (UUID:test-uuid; NAME:Arch) garlic-linux/v0.6.0.763) (MODEL:Garlic)';

		$this->indexService->setUID($ownerId);
		if (str_contains($serverName, 'localhost') || str_contains($serverName, 'garlic-hub.ddev.site'))
		{
			$this->indexService->handleIndexRequestForLocal($userAgent);
		}

		$filePath = $this->indexService->handleIndexRequest($userAgent);

		if ($filePath === '')
			return $response->withHeader('Content-Type', 'application/smil+xml')->withStatus(404);


		return $this->sendSmilHeader($response, $filePath);

	}


	private function sendSmilHeader(ResponseInterface $response, string $filePath): ResponseInterface
	{
		$lastModified = gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT';
		$cacheControl = 'public, must-revalidate, max-age=864000, pre-check=864000';

		$response = $response->withHeader('Cache-Control', $cacheControl);

		if (isset($_SERVER['If-Modified-Since']) && strtotime($_SERVER['If-Modified-Since']) === filemtime($filePath)) {
			return $response
				->withHeader('Last-Modified', $lastModified)
				->withStatus(304);
		}
		else
		{
			// not cached or cache outdated, 200 OK send index.smil
			$fileStream = new Stream(fopen($filePath, 'rb'));

			return $response
				->withBody($fileStream)
				->withHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT')
				->withHeader('Content-Length', (string)filesize($filePath))
				->withHeader('Content-Type', 'application/smil')
				->withHeader('Content-Description', 'File Transfer')
				->withHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"')
				->withStatus(200);
		}
	}
}
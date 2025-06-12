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


namespace App\Modules\Playlists\Controller;

use App\Modules\Playlists\Services\ExportService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class ExportController
{
	private ExportService $exportService;

	public function __construct(ExportService $exportService)
	{
		$this->exportService = $exportService;
	}

	/**
	 * @throws Exception
	 */
	public function export(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var array<string,mixed> $post */
		$post = $request->getParsedBody();

		if (!isset($post['playlist_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$session = $request->getAttribute('session');
		$this->exportService->setUID($session->get('user')['UID']);

		if ($this->exportService->exportToSmil($post['playlist_id']) === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist not found.']);

		return $this->jsonResponse($response, ['success' => true]);
	}

	private function jsonResponse(ResponseInterface $response, mixed $data): ResponseInterface
	{
		$json = json_encode($data);
		if ($json !== false)
			$response->getBody()->write($json);

		return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
	}
}
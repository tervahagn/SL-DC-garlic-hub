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

use App\Framework\Controller\AbstractAsyncController;
use App\Framework\Core\CsrfToken;
use App\Modules\Auth\UserSession;
use App\Modules\Playlists\Services\ExportService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
class ExportController extends AbstractAsyncController
{
	private readonly ExportService $exportService;
	private readonly UserSession $userSession;
	private readonly CsrfToken $csrfToken;

	public function __construct(ExportService $exportService, UserSession $userSession, CsrfToken $csrfToken)
	{
		$this->exportService = $exportService;
		$this->userSession = $userSession;
		$this->csrfToken = $csrfToken;
	}

	/**
	 * @throws Exception
	 */
	public function export(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{

		/** @var array<string,mixed> $post */
		$post = $request->getParsedBody();

		if (!$this->csrfToken->validateToken($post['csrf_token'] ?? ''))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'CsrF token mismatch.']);

		if (!isset($post['playlist_id']))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist ID not valid.']);

		$this->exportService->setUID($this->userSession->getUID());

		if ($this->exportService->exportToSmil($post['playlist_id']) === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Playlist not found.']);

		return $this->jsonResponse($response, ['success' => true]);
	}
}
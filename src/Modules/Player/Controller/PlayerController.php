<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2025 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
declare(strict_types=1);

namespace App\Modules\Player\Controller;

use App\Framework\Controller\AbstractAsyncController;
use App\Framework\Core\CsrfToken;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Modules\Player\Services\PlayerRestAPIService;
use App\Modules\Player\Services\PlayerService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlayerController extends AbstractAsyncController
{
	private readonly PlayerService $playerService;
	private readonly PlayerRestAPIService $playerRestAPIService;
	private readonly CsrfToken $csrfToken;

	public function __construct(PlayerService $indexService, PlayerRestAPIService $playerRestAPIService, CsrfToken $csrfToken)
	{
		$this->playerService  = $indexService;
		$this->playerRestAPIService = $playerRestAPIService;
		$this->csrfToken = $csrfToken;
	}

	public function replacePlaylist(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var array<string,string> $post */
		$post       = $request->getParsedBody();

		if (!$this->csrfToken->validateToken($post['csrf_token'] ?? ''))
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Csrf token mismatch.']);

		$playerId = (int) ($post['player_id'] ?? 0);
		if ($playerId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Player ID not valid.']);

		$playlistId = (int) ($post['playlist_id'] ?? 0);

		$session = $request->getAttribute('session');
		$this->playerService->setUID($session->get('user')['UID']);

		$data = $this->playerService->replaceMasterPlaylist($playerId, $playlistId);

		if (empty($data) || $data['affected'] === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => $this->playerService->getErrorMessages()]);

		return $this->jsonResponse($response, ['success' => true, 'playlist_name' => $data['playlist_name']]);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws FrameworkException
	 */
	public function pushPlaylist(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var array<string,string> $post */
		$post = $request->getParsedBody();

		if (!$this->csrfToken->validateToken($post['csrf_token'] ?? ''))
		{
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Csrf token mismatch.']);
		}

		$playerId = (int)($post['player_id'] ?? 0);
		if ($playerId === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Player ID not valid.']);

		$session = $request->getAttribute('session');
		$this->playerService->setUID($session->get('user')['UID']);

		$player = $this->playerService->fetchPlayer($playerId);
		if ($player === [])
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Player not found.']);

		if ($player['is_intranet'] === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Player is not reachable.']);

		if ($player['playlist_id'] === 0)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Player has no playlist assigned.']);

		$session = $request->getAttribute('session');
		$this->playerService->setUID($session->get('user')['UID']);
		$player = $this->playerService->fetchPlayer($playerId);
		if ($player === [])
			return $this->jsonResponse($response, ['success' => false, 'error_message' => 'Player is not accesible.']);

		$hasToken = $this->playerRestAPIService->authenticate($player['api_endpoint'], 'admin', '', $playerId);
		if (!$hasToken)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => ($this->playerRestAPIService->getErrorMessages()[0] ?? 'unknown player token error')]);

		$succeed = $this->playerRestAPIService->switchToDefaultContentUrl($player['api_endpoint'], $playerId);
		if (!$succeed)
			return $this->jsonResponse($response, ['success' => false, 'error_message' => ($this->playerRestAPIService->getErrorMessages()[0] ?? 'unknown player token error')]);

		return $this->jsonResponse($response, ['success' => true, 'message' => 'Playlist pushed successfully to '.$player['player_name'].'.']);
	}



}
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

use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Services\PlayerIndexService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlayerController
{
	private readonly PlayerIndexService $indexService;
	private readonly PlayerService $playerService;

	public function __construct(PlayerIndexService $indexService, PlayerService $playerService)
	{
		$this->indexService = $indexService;
		$this->playerService = $playerService;
	}

	/**
	 * @throws ModuleException
	 * @throws Exception
	 */
	public function index(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$ownerId = $_GET['owner_id'] ?? 0;
		$userAgent = $request->getAttribute('User-Agent');

		$data = $this->indexService->determineForIndexCreation($userAgent, $ownerId);

		if ($data === '')
			return $response->withHeader('Content-Type', 'application/smil+xml')->withStatus(404);

		$response->getBody()->write($data);
		return $response->withHeader('Content-Type', 'application/smil+xml')->withStatus(200);
	}

}
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
use App\Framework\Exceptions\ModuleException;
use App\Modules\Player\Services\PlayerIndexService;
use App\Modules\Player\Services\PlayerService;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlayerController
{
	private readonly PlayerIndexService $indexService;
	private readonly PlayerService $playerService;
	private readonly Sanitizer $sanitizer;

	public function __construct(PlayerIndexService $indexService, Sanitizer $sanitizer, PlayerService $playerService)
	{
		$this->indexService  = $indexService;
		$this->sanitizer     = $sanitizer;
		$this->playerService = $playerService;
	}

	public function index(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$ownerId   = $this->sanitizer->int($_GET['owner_id'] ?? 0);
		$userAgent = $this->sanitizer->string($request->getServerParams()['HTTP_USER_AGENT']);

		$data = $this->indexService->handleIndexRequest($userAgent, $ownerId);

		if ($data === '')
			return $response->withHeader('Content-Type', 'application/smil+xml')->withStatus(404);

		$response->getBody()->write($data);
		return $response->withHeader('Content-Type', 'application/smil+xml')->withStatus(200);
	}

}
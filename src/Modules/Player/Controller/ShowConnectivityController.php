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

use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Utils\Forms\FormTemplatePreparer;
use App\Modules\Player\Helper\NetworkSettings\Facade;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class ShowConnectivityController
{
	private readonly Facade $facade;
	private readonly FormTemplatePreparer $formElementPreparer;
	private Messages $flash;

	public function __construct(Facade $facade, FormTemplatePreparer $formElementPreparer, Messages $flash)
	{
		$this->facade = $facade;
		$this->formElementPreparer = $formElementPreparer;
		$this->flash = $flash;
	}

	/**
	 * @param array<string,string> $args
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws InvalidArgumentException
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$playerId = (int) ($args['player_id'] ?? 0);
		if ($playerId === 0)
			return $this->redirectWithErrors($response, 'No player id given.');

		$this->initFacade($request);

		$player = $this->facade->loadPlayerForEdit($playerId)->getPlayer();
		if ($player === [])
			return $this->redirectWithErrors($response, 'Player ID not accessible.');

		/** @var array{player_id:int, player_name:string, model:int, is_intranet:int, api_endpoint:string} $player */
		return $this->outputRenderedForm($response, $player);
	}

	/**
	 * @param array{player_id:int, player_name:string, model:int, is_intranet:int, api_endpoint:string} $player
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	private function outputRenderedForm(ResponseInterface $response, array $player): ResponseInterface
	{
		$dataSections = $this->facade->prepareUITemplate($player);
		$templateData = $this->formElementPreparer->prepareUITemplate($dataSections);

		$response->getBody()->write(serialize($templateData));
		return $response->withHeader('Content-Type', 'text/html')->withStatus(200);
	}

	private function initFacade(ServerRequestInterface $request): void
	{
		$this->flash      = $request->getAttribute('flash');
		$this->facade->init($request->getAttribute('translator'), $request->getAttribute('session'));
	}


	private function redirectWithErrors(ResponseInterface $response, string $defaultMessage = 'Unknown error.'): ResponseInterface
	{
		$this->flash->addMessage('error', $defaultMessage);
		return $response->withHeader('Location', '/player')->withStatus(302);
	}
}
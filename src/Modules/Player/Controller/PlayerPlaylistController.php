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
use App\Modules\Player\Helper\PlayerPlaylist\InputHandler;
use App\Modules\Player\Services\PlayerRestAPIService;
use App\Modules\Player\Services\PlayerService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlayerPlaylistController extends AbstractAsyncController
{
	public function __construct(private readonly InputHandler $inputHandler)
	{
	}

	public function replacePlaylist(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var array<string,string> $input */
		$input = $request->getParsedBody();

		$answer = $this->inputHandler->setInput($input)->validateForReplacePlaylist($response);
		if ($answer !== null)
			return $answer;

		return $this->inputHandler->replaceMasterPlaylist($response);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 * @throws FrameworkException
	 */
	public function pushPlaylist(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		/** @var array<string,string> $input */
		$input = $request->getParsedBody();

		$answer = $this->inputHandler->setInput($input)->validateStandardInput($response);
		if ($answer !== null)
			return $answer;

		$answer = $this->inputHandler->checkPlayer($response);
		if ($answer !== null)
			return $answer;

		return $this->inputHandler->pushPlaylist($response);
	}



}
<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
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


namespace App\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{
	private array $publicRoutes = [
		'/login',
		'/set-locales',
		'/register',
		'/reset-password',
		'/legals',
		'/privacy',
		'/terms',
		'/cms'
	];

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		// if public route then skip
		// todo: check if an anonymous middleware would be more efficient
		preg_match('~^/([^/]+)~', $request->getUri()->getPath(), $matches);
		if (!isset($matches[0]) || in_array($matches[0], $this->publicRoutes, true))
			return $handler->handle($request);

		$session = $request->getAttribute('session');

		if (!$session->exists('user'))
		{
			$response = new Response();
			return $response->withHeader('Location', '/login')->withStatus(302);
		}

		return $handler->handle($request);
	}
}
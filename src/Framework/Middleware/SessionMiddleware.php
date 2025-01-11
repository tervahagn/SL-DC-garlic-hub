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

use App\Framework\Core\Cookie;
use App\Framework\Core\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Flash\Messages;

class SessionMiddleware implements MiddlewareInterface
{
	private Session $session;
	private Cookie $cookie;
	private Messages $flash;

	public function __construct(Session $session, Messages $flash, Cookie $cookie)
	{
		$this->session = $session;
		$this->cookie  = $cookie;
		$this->flash   = $flash;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$request = $request->withAttribute('session', $this->session);
		$request = $request->withAttribute('cookie', $this->cookie);
		$request = $request->withAttribute('flash', $this->flash);

		return $handler->handle($request);
	}
}

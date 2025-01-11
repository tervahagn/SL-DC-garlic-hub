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
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\UserException;
use App\Modules\Auth\AuthService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{

	private AuthService $authService;

	private array $publicRoutes = [
		'set-locales',
		'register',
		'reset-password',
		'legals',
		'privacy',
		'terms',
		'cms'
	];

	/**
	 * @param AuthService $authService
	 */
	public function __construct(AuthService $authService)
	{
		$this->authService = $authService;
	}

	/**
	 * @throws UserException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws FrameworkException
	 * @throws Exception
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		// check for public route and skip authentication
		preg_match('~^/([^/]*)~', $request->getUri()->getPath(), $matches);
		if (!isset($matches[1]) || in_array($matches[1], $this->publicRoutes, true))
			return $handler->handle($request);

		/** @var Session $session */
		$session = $request->getAttribute('session');
		if ($session === null)
			return $this->respondNonAuth($matches[1], $request, $handler);

		if (!$session->exists('user'))
		{
			/** @var Cookie $cookie */
			$cookie     = $request->getAttribute('cookie');
			if ($cookie === null)
				return $this->respondNonAuth($matches[1], $request, $handler);

			$userEntity = null;
			if ($cookie->hasCookie(AuthService::COOKIE_NAME_AUTO_LOGIN))
				$userEntity = $this->authService->loginByCookie();

			if ($userEntity === null)
				return $this->respondNonAuth($matches[1], $request, $handler);

			$main_data = $userEntity->getMain();
			$session->set('user', $main_data);
			$session->set('locale', $main_data['locale']);
		}

		return $this->respondAuth($matches[1], $request, $handler);
	}

	private function respondAuth(string $path, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = new Response();
		if ($path === 'login')
			return $response->withHeader('Location', '/')->withStatus(302);
		else
			return $handler->handle($request);
	}
	private function respondNonAuth(string $path, ServerRequestInterface $request, RequestHandlerInterface $handler):	ResponseInterface
	{
		$response = new Response();
		if ($path === 'login')
			return $handler->handle($request);
		else
			return $response->withHeader('Location', '/login')->withStatus(302);
	}

}
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

namespace App\Controller;

use App\Framework\Core\Locales\Locales;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;

class HomeController
{
	public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$session = $request->getAttribute('session');
		if (!$this->isUserLoggedIn($session))
			return $this->redirectToLogin($response);

		$data = $this->generateHomePageData($session);
		$this->writeResponseData($response, $data);

		return $this->setContentType($response);
	}

	public function setLocales(ServerRequestInterface $request, ResponseInterface $response, array $args):
	ResponseInterface
	{
		$locale  = htmlentities($args['locale'], ENT_QUOTES);

		// set locale into session
		/** @var  Helper $session */
		$session = $request->getAttribute('session');
		$session->set('locale', $locale);

		// determine current locale secure because it checks a whitelist
		// of available locales
		/** @var  Locales $locales */
		$locales    = $request->getAttribute('locales');
		$locales->determineCurrentLocale();
		$previousUrl = $request->getHeaderLine('Referer') ?: '/';

		return $response
			->withHeader('Location', $previousUrl)
			->withStatus(302); // 302: forwarding

	}

	private function isUserLoggedIn($session): bool
	{
		return $session->exists('user');
	}

	private function redirectToLogin(ResponseInterface $response): ResponseInterface
	{
		return $response->withHeader('Location', '/login')->withStatus(302);
	}

	private function generateHomePageData(Helper $session): array
	{
		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => 'Garlic Hub - Home',
			],
			'this_layout' => [
				'template' => 'home',
				'data' => [
					'LANG_PAGE_HEADER' => 'Welcome',
					'LANG_CONTENT' => 'Yes! This is our starting homepage. And I know is is pretty useless to welcome people here. But hey, it is a start. So, do not overestimate it. At the end it is some more entertaining than this boring Lorem Ipsum text. So, enjoy your stay!',
					'SHOW_SESSION' => print_r($session->get('user'), true),
				],
			],
		];
	}

	private function writeResponseData(ResponseInterface $response, array $data): void
	{
		$response->getBody()->write(serialize($data));
	}

	private function setContentType(ResponseInterface $response): ResponseInterface
	{
		return $response->withHeader('Content-Type', 'text/html');
	}

}
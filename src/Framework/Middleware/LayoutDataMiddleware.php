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
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Middleware\Session;
use SlimSession\Helper;

/**
 * The LayoutDataMiddleware class adds common layout data
 * (such as metadata, menu items, and legal information) to the request,
 * making it available for use in templates.
 * It then passes the request to the next middleware
 * or handler in the pipeline.
 */
class LayoutDataMiddleware implements MiddlewareInterface
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$session = $request->getAttribute('session');

		$locale = substr($session->get('user')['locale'], 0, 2);
		$layoutData = [
			'main_menu' => $this->createMainMenu($session),
			'CURRENT_LOCALE_LOWER' => strtolower($locale),
			'CURRENT_LOCALE_UPPER' => strtoupper($locale),
			'language_select' => $this->createLanguageSelect(),
			'user_menu' => $this->createUserMenu($session),

			'LANG_LEGALS' => 'Web Legals',
			'LANG_PRIVACY' => 'Privacy',
			'LANG_TERMS' => 'Terms'
		];

		// Daten dem Request hinzufügen
		$request = $request->withAttribute('layoutData', $layoutData);

		// Weiter zur nächsten Middleware oder zum Handler
		return $handler->handle($request);
	}

	private function createMainMenu(Helper $session): array
	{
		$menu    = [['URL' => '/login', 'LANG_MENU_POINT' => 'Login']];
		if ($session->exists('user'))
		{
			$menu = [
				['URL' => '/player', 'LANG_MENU_POINT' => 'Player'],
				['URL' => '/playlists', 'LANG_MENU_POINT' => 'Playlists'],
				['URL' => '/mediapool', 'LANG_MENU_POINT' => 'Mediapool']
			];
		}
		return $menu;
	}

	private function createUserMenu(Helper $session): array
	{
		$user_menu    = [];
		if ($session->exists('user'))
		{
			$user_menu = [
				[
					'LANG_LOGIN_AS' => 'Logged in as',
					'USERNAME'      => $session->get('user')['username'],
					'LANG_MANAGE_ACCOUNT' => 'Manage Account',
					'LANG_LOGOUT' => 'Logout'
				],
			];
		}
		return $user_menu;
	}

	private function createLanguageSelect(): array
	{
		return [
			['LOCALE_LONG' => 'en_US', 'LOCALE_SMALL' => 'en', 'LANGUAGE_NAME' => 'English'],
			['LOCALE_LONG' => 'de_DE', 'LOCALE_SMALL' => 'de', 'LANGUAGE_NAME' => 'German'],
			['LOCALE_LONG' => 'es_ES', 'LOCALE_SMALL' => 'es', 'LANGUAGE_NAME' => 'Spanish'],
			['LOCALE_LONG' => 'el_GR', 'LOCALE_SMALL' => 'el', 'LANGUAGE_NAME' => 'Greek'],
			['LOCALE_LONG' => 'fr_FR', 'LOCALE_SMALL' => 'fr', 'LANGUAGE_NAME' => 'French'],
			['LOCALE_LONG' => 'ru_RU', 'LOCALE_SMALL' => 'ru', 'LANGUAGE_NAME' => 'Russian'],
		];
	}


}

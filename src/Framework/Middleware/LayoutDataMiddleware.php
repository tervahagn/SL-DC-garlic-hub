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

use App\Framework\Core\Config\Config;
use App\Framework\Core\Locales\Locales;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\InvalidArgumentException;
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
	private Translator $translator;
	private Helper $session;

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$this->session    = $request->getAttribute('session');
        $this->translator = $request->getAttribute('translator');
		$locales = $request->getAttribute('locales');

		/** @var Config $config */
		$config = $request->getAttribute('config');
		$locale = $locales->getLanguageCode();
		$layoutData = [
			'main_menu' => $this->createMainMenu(),
			'CURRENT_LOCALE_LOWER' => strtolower($locale),
			'CURRENT_LOCALE_UPPER' => strtoupper($locale),
			'language_select' => $this->createLanguageSelect(),
			'user_menu' => $this->createUserMenu(),

			'APP_NAME' => $config->getEnv('APP_NAME'),
			'LANG_LEGAL_NOTICE' => $this->translator->translate('legal_notice', 'menu'),
			'LANG_PRIVACY' => $this->translator->translate('privacy', 'menu'),
			'LANG_TERMS' => $this->translator->translate('terms', 'menu')
		];

		$request = $request->withAttribute('layoutData', $layoutData);
		return $handler->handle($request);
	}

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	private function createMainMenu(): array
	{
		if ($this->session->exists('user'))
		{
			return [
				['URL' => '/player', 'LANG_MENU_POINT' => $this->translator->translate('player', 'menu')],
				['URL' => '/playlists', 'LANG_MENU_POINT' => $this->translator->translate('playlists', 'menu')],
				['URL' => '/mediapool', 'LANG_MENU_POINT' => $this->translator->translate('mediapool', 'menu')]
			];
		}

		return [['URL' => '/login', 'LANG_MENU_POINT' => $this->translator->translate('login', 'login')]];
	}

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	private function createUserMenu(): array
	{
		if (!$this->session->exists('user') && empty($this->session->get('user')))
			return [];

		$user     = $this->session->get('user');
		$username = is_array($user) && array_key_exists('username', $user) ? $user['username'] : '';

		return [
			[
				'LANG_LOGIN_AS' => $this->translator->translate('logged_in_as', 'menu'),
				'USERNAME'      => $username,
				'LANG_MANAGE_ACCOUNT' => $this->translator->translate('manage_account', 'menu'),
				'LANG_LOGOUT' => $this->translator->translate('logout', 'menu')
			]
		];
	}

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 */
	private function createLanguageSelect(): array
	{
		$languages = $this->translator->translateArrayForOptions('languages', 'menu');
		$ret = [];
		foreach ($languages as $key  => $value)
		{
			$ret[] = ['LOCALE_LONG' => $key, 'LOCALE_SMALL' => substr($key, 0, 2), 'LANGUAGE_NAME' => $value];
		}

		return $ret;
	}
}

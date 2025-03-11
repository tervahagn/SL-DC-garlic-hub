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
use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\TemplateEngine\AdapterInterface;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;
use Slim\Psr7\Stream;

/**
 * Middleware that finalizes the response by rendering the layout or template.
 * It adds execution time and memory usage statistics for non-API routes.
 */
class FinalRenderMiddleware implements MiddlewareInterface
{
	private Translator $translator;
	private Session $session;

	private AdapterInterface $templateService;

	/**
	 * @param AdapterInterface $templateService
	 */
	public function __construct(AdapterInterface $templateService)
	{
		$this->templateService = $templateService;
	}

	/**
	 * @param ServerRequestInterface  $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);

		$this->session    = $request->getAttribute('session');
		$this->translator = $request->getAttribute('translator');
		$locales = $request->getAttribute('locales');
		/** @var Config $config */
		$config = $request->getAttribute('config');
		$locale = $locales->getLanguageCode();

		$layoutData = [
			'messages' => $this->outputFlashMessages($request),
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

		if ($_ENV['APP_DEBUG'])
		{
			$start_time   = $request->getAttribute('start_time');
			$start_memory = $request->getAttribute('start_memory');
			$memory_usage = memory_get_usage() - $start_memory;
			$layoutData['EXECUTION_TIME']    = number_format(microtime(true) - $start_time, 6).'sec';
			$layoutData['MEMORY_USAGE']      = round($memory_usage / 1024, 2) . ' KB';
			$layoutData['PEAK_MEMORY_USAGE'] = round(memory_get_peak_usage() / 1024, 2) . ' KB';
		}

		$controllerData = @unserialize((string) $response->getBody());

		if ($controllerData === false)
			return $response->withHeader('Content-Type', 'text/html');

		$mainContent = $this->templateService->render($controllerData['this_layout']['template'], $controllerData['this_layout']['data']);

		$finalContent = $this->templateService->render('layouts/main_layout', array_merge($layoutData,
			['MAIN_CONTENT' => $mainContent], $controllerData['main_layout']));

		$response = $response->withBody(new Stream(fopen('php://temp', 'r+')));
		$response->getBody()->write($finalContent);

		return $response->withHeader('Content-Type', 'text/html');
	}

	private function outputFlashMessages(ServerRequestInterface $request): array
	{
		/** @var Messages $flash */
		$flash    = $request->getAttribute('flash');
		$messages = [];
		// errors have a close button, successes close after 5s (set in css)
		foreach (['error' => true, 'success' => false] as $type => $hasCloseButton)
		{
			if ($flash->hasMessage($type))
			{
				foreach ($flash->getMessage($type) as $message)
				{
					$messages[] = ['MESSAGE_TYPE' => $type,	'has_close_button' => $hasCloseButton, 'MESSAGE_TEXT' => $message];
				}
			}
		}
		return $messages;
	}

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
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
	 * @throws FrameworkException|PhpfastcacheSimpleCacheException
	 */
	private function createUserMenu(): array
	{
		if (!$this->session->exists('user') && empty($this->session->get('user')))
			return [];

		$user     = $this->session->get('user');
		$username = is_array($user) && array_key_exists('username', $user) ? $user['username'] : '';

		return [
			[
				'LANG_LOGIN_AS'       => $this->translator->translate('logged_in_as', 'menu'),
				'USERNAME'            => $username,
				'LANG_MANAGE_ACCOUNT' => $this->translator->translate('manage_account', 'menu'),
				'LANG_LOGOUT'         => $this->translator->translate('logout', 'menu')
			]
		];
	}

	/**
	 * @throws CoreException
	 * @throws InvalidArgumentException|PhpfastcacheSimpleCacheException
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

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

namespace App\Modules\Playlists\Controller;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\FormHelper\SettingsFormBuilder;
use App\Modules\Playlists\FormHelper\SettingsValidator;
use App\Modules\Playlists\PlaylistMode;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class SettingsController
{
	private readonly SettingsFormBuilder $settingsFormBuilder;
	private readonly SettingsValidator $settingsValidator;
	private readonly PlaylistsService $playlistsService;
	private Translator $translator;
	private int $UID;
	private Session $session;
	private Messages $flash;

	public function __construct(SettingsFormBuilder $formBuilder, SettingsValidator $settingsValidator, PlaylistsService $playlistsService)
	{
		$this->settingsFormBuilder = $formBuilder;
		$this->settingsValidator = $settingsValidator;
		$this->playlistsService = $playlistsService;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$this->setImportantAttributes($request);
		$playlist = ['playlist_mode' => $args['playlist_mode'] ?? 'master'];

		$response->getBody()->write(serialize($this->buildForm($playlist)));
		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \App\Framework\Exceptions\FrameworkException
	 * @throws \App\Framework\Exceptions\ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$this->setImportantAttributes($request);

		$playlistId = $this->settingsValidator->validatePlaylistId($args);
		if ($playlistId === null)
			return $this->redirectWithError($response, 'Playlist ID not valid.', '/playlists');

		$playlist = $this->playlistsService->loadPlaylistForEdit($playlistId);
		if (empty($playlist))
			return $this->redirectWithError($response, 'Playlist not found.', '/playlists');

		return $this->returnBuildForm($response, $playlist);
	}

	/**
	 * @throws \App\Framework\Exceptions\ModuleException
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 * @throws \App\Framework\Exceptions\FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->setImportantAttributes($request);
		$this->flash = $request->getAttribute('flash');

		$post = $request->getParsedBody();

		if (!$this->settingsValidator->validateCreate($post, $this->flash))
			return $this->returnBuildForm($response, $post);

		if (isset($playlist['playlist_id']) && !isset($playlist['playlist_mode']) && $this->playlistsService->update($post) > 0)
			return $this->redirectSucceed($response, 'Playlist '.$playlist['playlist_id'].' successfully updated.', '/playlists');


		if (!isset($playlist['playlist_id']) && isset($playlist['playlist_mode']) && $this->playlistsService->create($post))
				return $this->redirectSucceed($response, 'Playlist '.$playlist['playlist_id'].' successfully created.', '/playlists');

		return $this->returnBuildForm($response, $post);
	}

	/**
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 * @throws \App\Framework\Exceptions\FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function returnBuildForm(ResponseInterface $response, $post): ResponseInterface
	{
		$data = $this->buildForm($post);
		$response->getBody()->write(serialize($data));
		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 * @throws \App\Framework\Exceptions\FrameworkException
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function buildForm(array $playlist): array
	{
		$elements = $this->settingsFormBuilder->init($this->translator, $this->session)->createForm($playlist);

		$title = $this->translator->translate('settings', 'playlists'). ' - ' .
			$this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists')[strtolower($this->playlistMode)];

		$data = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $title,
				'additional_css' => ['/css/playlists/settings.css']
			],
			'this_layout' => [
				'template' => 'generic/edit', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $title,
					'SITE' => '/playlists/settings',
					'element_hidden' => $elements['hidden'],
					'form_element' => $elements['visible'],
					'form_button' => [
						[
							'ELEMENT_BUTTON_TYPE' => 'submit',
							'ELEMENT_BUTTON_NAME' => 'submit',
							'LANG_ELEMENT_BUTTON' => $this->translator->translate('save', 'main')
						]
					]
				]
			]
		];

		return $data;
	}

	private function setImportantAttributes(ServerRequestInterface $request): void
	{
		$this->translator = $request->getAttribute('translator');
		$this->session    = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);
		$this->flash      = $request->getAttribute('flash');
	}

	private function redirectWithError(ResponseInterface $response, string $message, string $url): ResponseInterface
	{
		$this->flash->addMessage('error', $message);
		return $response->withHeader('Location', $url)->withStatus(302);
	}

	private function redirectSucceed(ResponseInterface $response, string $message, string $url): ResponseInterface
	{
		$this->flash->addMessage('error', $message);
		return $response->withHeader('Location', $url)->withStatus(302);
	}
}
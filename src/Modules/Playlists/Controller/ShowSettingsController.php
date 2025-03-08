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
use App\Modules\Playlists\FormHelper\SettingsParameters;
use App\Modules\Playlists\FormHelper\SettingsFormBuilder;
use App\Modules\Playlists\Services\PlaylistsEditService;
use App\Modules\Playlists\Services\ResultList;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Slim\Flash\Messages;

class ShowSettingsController
{
	private readonly SettingsFormBuilder $settingsFormBuilder;
	private readonly PlaylistsEditService $playlistsService;
	private readonly SettingsParameters $settingsParameters;
	private Translator $translator;
	private Session $session;
	private Messages $flash;

	public function __construct(SettingsFormBuilder $formBuilder, SettingsParameters $settingsParameters, PlaylistsEditService $playlistsService)
	{
		$this->settingsFormBuilder = $formBuilder;
		$this->settingsParameters  = $settingsParameters;
		$this->playlistsService    = $playlistsService;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws Exception
	 * @throws ModuleException
	 */
	public function newPlaylistForm(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$this->setImportantAttributes($request);
		$playlist = ['playlist_mode' => $args['playlist_mode'] ?? 'master'];

		$this->settingsFormBuilder->init($this->translator, $this->session);
		$this->settingsFormBuilder->buildCreateNewParameter($playlist['playlist_mode']);

		return $this->returnBuildForm($response, $playlist);
	}


	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function editPlaylistForm(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$this->setImportantAttributes($request);

		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->redirectWithError($response, 'Playlist ID not valid.');

		$playlist = $this->playlistsService->loadPlaylistForEdit($playlistId);
		if (empty($playlist))
			return $this->redirectWithError($response, 'Playlist not found.');

		$this->settingsFormBuilder->init($this->translator, $this->session);
		$this->settingsFormBuilder->buildEditParameter($playlist);

		return $this->returnBuildForm($response, $playlist);
	}

	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
	 */
	public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$this->setImportantAttributes($request);
		$playlistId = (int) $args['playlist_id'] ?? 0;
		if ($playlistId === 0)
			return $this->redirectWithError($response, 'Playlist ID not valid.');

		if ($this->playlistsService->delete($playlistId) === 0)
			return $this->redirectWithError($response, 'Playlist not found.');

		return $this->redirectSucceed($response, 'Playlist successful deleted');
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws Exception
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->setImportantAttributes($request);
		$this->flash = $request->getAttribute('flash');
		$post        = $request->getParsedBody();

		// 1. Set the parameters for the form
		$this->settingsFormBuilder->init($this->translator, $this->session);
		if (isset($post['playlist_id']) && $post['playlist_id'] > 0)
		{
			$playlist = $this->playlistsService->loadPlaylistForEdit($post['playlist_id']);
			$this->settingsFormBuilder->buildEditParameter($playlist);
		}
		else
		{
			$this->settingsFormBuilder->buildCreateNewParameter($post['playlist_mode']);
		}

		// 2. Sanitize and Validate userInput and parameters
		$errors = $this->settingsFormBuilder->handleUserInput($post);
		foreach ($errors as $errorText)
		{
			$this->flash->addMessageNow('error', $errorText);
		}

		// 3. if there are errors build the form again
		if (!empty($errors))
			return $this->returnBuildForm($response, $post);

		// 3. When no errors store
		if (isset($post['playlist_id']) && $post['playlist_id'] > 0)
			return $this->storeUpdatedPlaylist($response, $post);
		else
			return $this->storeNewPlaylist($response, $post);

	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws Exception
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	private function storeNewPlaylist(ResponseInterface $response, array $post): ResponseInterface
	{
		$saveData  = array_combine(
			$this->settingsParameters->getInputParametersKeys(),
			$this->settingsParameters->getInputValuesArray()
		);

		$id = $this->playlistsService->createNew($saveData);
		if ($id > 0)
			return $this->redirectSucceed($response, 'Playlist “'.$post['playlist_name'].'“ successfully created.');

		return $this->returnBuildForm($response, $post);
	}

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws Exception
	 */
	private function storeUpdatedPlaylist(ResponseInterface $response, array $post): ResponseInterface
	{
		$saveData  = array_combine(
			$this->settingsParameters->getInputParametersKeys(),
			$this->settingsParameters->getInputValuesArray()
		);

		if ($this->playlistsService->update($saveData) > 0)
			return $this->redirectSucceed($response, 'Playlist “'.$post['playlist_name'].'“ successfully updated.');

		return $this->returnBuildForm($response, $post);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 * @throws ModuleException
	 */
	private function returnBuildForm(ResponseInterface $response, $post): ResponseInterface
	{
		$elements = $this->settingsFormBuilder->buildForm($post);
		$data = $this->buildForm($elements, $post['playlist_mode']);

		$response->getBody()->write(serialize($data));
		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	private function buildForm(array $elements, string $playlistMode): array
	{
		$title = $this->translator->translate('settings', 'playlists'). ' - ' .
			$this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists')[strtolower($playlistMode)];

		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $title,
				'additional_css' => ['/css/playlists/settings.css']
			],
			'this_layout' => [
				'template' => 'playlists/edit', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $title,
					'FORM_ACTION' => '/playlists/settings',
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
	}

	private function setImportantAttributes(ServerRequestInterface $request): void
	{
		$this->translator = $request->getAttribute('translator');
		$this->session    = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);
		$this->flash      = $request->getAttribute('flash');
	}

	private function redirectWithError(ResponseInterface $response, string $message): ResponseInterface
	{
		$this->flash->addMessage('error', $message);
		return $response->withHeader('Location', '/playlists')->withStatus(302);
	}

	private function redirectSucceed(ResponseInterface $response, string $message): ResponseInterface
	{
		$this->flash->addMessage('success', $message);
		return $response->withHeader('Location', '/playlists')->withStatus(302);
	}
}
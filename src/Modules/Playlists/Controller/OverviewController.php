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
use App\Modules\Playlists\FormHelper\SettingsFormBuilder;
use App\Modules\Playlists\FormHelper\SettingsValidator;
use App\Modules\Playlists\Services\PlaylistsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;

class OverviewController
{
	private readonly SettingsFormBuilder $settingsFormBuilder;
	private readonly PlaylistsService $playlistsService;
	private Translator $translator;
	private Session $session;

	public function __construct(SettingsFormBuilder $formBuilder, PlaylistsService $playlistsService)
	{
		$this->settingsFormBuilder = $formBuilder;
		$this->playlistsService = $playlistsService;
	}

	public function show(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$playlists = $this->playlistsService->loadPlaylistsForOverview();


		echo 'Playlist Overview';
		return $response->withHeader('Content-Type', 'text/html');
	}


	private function buildForm(array $playlist): array
	{
		$elements = $this->settingsFormBuilder->init($this->translator, $this->session)->createForm($playlist);

		$title = $this->translator->translate('settings', 'playlists'). ' - ' .
			$this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists')[strtolower($playlist['playlist_mode'])];

		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $title,
				'additional_css' => ['/css/playlists/settings.css']
			],
			'this_layout' => [
				'template' => 'playlists/edit', // Template-name
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
	}

	private function setImportantAttributes(ServerRequestInterface $request): void
	{
		$this->translator = $request->getAttribute('translator');
		$this->session    = $request->getAttribute('session');
		$this->playlistsService->setUID($this->session->get('user')['UID']);
		$this->flash      = $request->getAttribute('flash');
	}

}
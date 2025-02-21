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
use App\Modules\Playlists\PlaylistMode;
use App\Modules\Playlists\Services\PlaylistsService;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;

class SettingsController
{
	private readonly FormBuilder $formBuilder;
	private readonly PlaylistsService $playlistsService;
	private Translator $translator;
	private int $UID;
	private Session $session;
	private string $playlistMode;
	/**
	 * @param FormBuilder $formBuilder
	 * @param PlaylistsService $playlistsService
	 */
	public function __construct(FormBuilder $formBuilder, PlaylistsService $playlistsService)
	{
		$this->formBuilder = $formBuilder;
		$this->playlistsService = $playlistsService;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws Exception
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
	{
		$this->translator = $request->getAttribute('translator');
		$this->session    = $request->getAttribute('session');

		$this->playlistsService->setUID($this->session->get('user')['UID']);

		$playlist_id = $args['playlist_id'] ?? 0;
		$this->playlistMode = $args['playlist_mode'] ?? 'master';


		$playlist = [];
		if ($playlist_id > 0)
			$playlist = $this->playlistsService->loadPlaylistForEdit($playlist_id);

		$hiddenElements = [];
		$formElements = [];

		/** @var FieldInterface $element */
		foreach ($this->createForm($playlist) as $key => $element)
		{
			if ($key === 'csrf_token')
			{
				$hiddenElements[] = [
					'HIDDEN_HTML_ELEMENT'        => $this->formBuilder->renderField($element)
				];
				continue;
			}

			$formElements[] = [
				'HTML_ELEMENT_ID'    => $element->getId(),
				'LANG_ELEMENT_NAME'  => $element->getLabel(),
				'ELEMENT_MUST_FIELD' => '', //$element->getAttribute('required') ? '*' : '',
				'HTML_ELEMENT'       => $this->formBuilder->renderField($element)
			];
		}
		$data = [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $this->translator->translate('settings', 'playlists'),
				'additional_css' => ['/css/playlists/settings.css']
			],
			'this_layout' => [
				'template' => 'generic/edit', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $this->translator->translate('settings', 'playlists'),
					'SITE' => '/playlists/settings',
					'element_hidden' => $hiddenElements,
					'form_element' => $formElements,
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
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');

	}


	/**
	 * @throws FrameworkException
	 * @throws CoreException
	 * @throws Exception
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	private function createForm(array $playlist): array
	{
		$form       = [];
		$rules      = ['required' => true, 'minlength' => 2];

		$form['playlist_name'] = $this->formBuilder->createField([
			'type' => FieldType::TEXT,
			'id' => 'playlist_name',
			'name' => 'playlist_name',
			'title' => $this->translator->translate('playlist_name', 'playlists'),
			'label' => $this->translator->translate('playlist_name', 'playlists'),
			'value' => $playlist['name'] ?? '',
			'rules' => $rules,
			'default_value' => ''
		]);

		$form['UID'] = $this->formBuilder->createField([
			'type' => FieldType::AUTOCOMPLETE,
			'id' => 'UID',
			'name' => 'UID',
			'title' => $this->translator->translate('owner', 'main'),
			'label' => $this->translator->translate('owner', 'main'),
			'data-id' => $playlist['UID'] ?? $this->session->get('user')['UID'],
			'value' => $playlist['username'] ?? $this->session->get('user')['username'],
			'default_value' => ''
		]);

		if ($this->playlistsService->isModuleadmin() &&
			($this->playlistMode === PlaylistMode::MASTER->value || $this->playlistMode === PlaylistMode::INTERNAL->value))
		{
			$form['time_limit'] = $this->formBuilder->createField([
				'type' => FieldType::NUMBER,
				'id' => 'time_limit',
				'name' => 'time_limit',
				'title' => $this->translator->translate('time_limit_explanation', 'playlists'),
				'label' => $this->translator->translate('time_limit', 'playlists'),
				'value' => $playlist['time_limit'] ?? 0,
				'min'   => 0,
				'default_value' => 0
			]);
		}

		$form['csrf_token'] = $this->formBuilder->createField([
			'type' => FieldType::CSRF,
			'id' => 'csrf_token',
			'name' => 'csrf_token',
		]);

		return $form;

	}


}
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

namespace App\Modules\Playlists\FormHelper;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\PlaylistMode;
use App\Modules\Playlists\Services\AclValidator;

readonly class SettingsFormBuilder
{
	private FormBuilder $formBuilder;
	private Translator $translator;
	private Session $session;
	private AclValidator $aclValidator;

	/**
	 * @param \App\Modules\Playlists\Services\AclValidator $aclValidator
	 * @param \App\Framework\Utils\Html\FormBuilder $formBuilder
	 */
	public function __construct(AclValidator $aclValidator, FormBuilder $formBuilder)
	{
		$this->aclValidator = $aclValidator;
		$this->formBuilder = $formBuilder;
	}

	public function init($translator, $session): static
	{
		$this->translator = $translator;
		$this->session = $session;

		return $this;
	}

	/**
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws \App\Framework\Exceptions\FrameworkException
	 */
	public function createForm(array $playlist): array
	{
		$form = $this->collectFormElements($playlist);
		return $this->formBuilder->createFormular($form);
	}

		/**
	 * @throws \App\Framework\Exceptions\CoreException
	 * @throws \App\Framework\Exceptions\FrameworkException
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function collectFormElements(array $playlist): array
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
			'value' => $playlist['UID'] ?? $this->session->get('user')['UID'],
			'data-label' => $playlist['username'] ?? $this->session->get('user')['username'],
			'default_value' => ''
		]);

		if ($this->aclValidator->isModuleadmin($this->session->get('user')['UID']) &&
			in_array($playlist['playlist_mode'], [PlaylistMode::MASTER->value, PlaylistMode::INTERNAL->value]))
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

		// PlaylistMode can be set only on create.
		if (isset($playlist['playlist_id']))
		{
			$form['playlist_id'] = $this->formBuilder->createField([
				'type' => FieldType::HIDDEN,
				'id' => 'playlist_id',
				'name' => 'playlist_id',
				'value' => $playlist['playlist_id'],
			]);
		}
		else
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::HIDDEN,
				'id' => 'playlist_mode',
				'name' => 'playlist_mode',
				'value' => $playlist['playlist_mode'],
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
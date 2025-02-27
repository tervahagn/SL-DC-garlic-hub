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
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Services\AclValidator;

class FilterFormBuilder
{
	private FormBuilder $formBuilder;
	private Translator $translator;
	private AclValidator $aclValidator;
	private FilterParameters $parameters;
	private int $UID;
	private string $username;

	public function __construct(AclValidator $aclValidator, FilterParameters $parameters, FormBuilder $formBuilder)
	{
		$this->aclValidator = $aclValidator;
		$this->parameters   = $parameters;
		$this->formBuilder  = $formBuilder;
	}

	public function init(Translator $translator, Session $session): static
	{
		$this->translator = $translator;
		$this->UID      = $session->get('user')['UID'];
		$this->username = $session->get('user')['username'];

		return $this;
	}

	public function buildForm(array $filter): array
	{
		$form = $this->collectFormElements($filter);
		return $this->formBuilder->createFormular($form);
	}

	public function collectFormElements(array $filter): array
	{
		$form       = [];
		$rules      = ['required' => true, 'minlength' => 2];

		$form['playlist_name'] = $this->formBuilder->createField([
			'type' => FieldType::TEXT,
			'id' => 'playlist_name',
			'name' => 'playlist_name',
			'title' => $this->translator->translate('playlist_name', 'playlists'),
			'label' => $this->translator->translate('playlist_name', 'playlists'),
			'value' => $filter[SettingsParameters::PARAMETER_PLAYLIST_NAME] ?? '',
			'rules' => $rules,
			'default_value' => ''
		]);

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_USERNAME))
		{
			$form['UID'] = $this->formBuilder->createField([
				'type' => FieldType::AUTOCOMPLETE,
				'id' => 'UID',
				'name' => 'UID',
				'title' => $this->translator->translate('owner', 'main'),
				'label' => $this->translator->translate('owner', 'main'),
				'value' => $filter[FilterParameters::PARAMETER_USERNAME] ?? $this->UID,
				'data-label' => $filter['username'] ?? $this->username,
				'default_value' =>  $this->UID
			]);
		}

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_PLAYLIST_MODE))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::HIDDEN,
				'id' => 'playlist_mode',
				'name' => 'playlist_mode',
				'value' => $filter[FilterParameters::PARAMETER_PLAYLIST_MODE],
			]);
		}

		return $form;
	}

}
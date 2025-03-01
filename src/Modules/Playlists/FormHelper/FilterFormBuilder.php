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
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Playlists\Services\AclValidator;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

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

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function collectFormElements(array $filter): array
	{
		$form       = [];
		$form['playlist_name'] = $this->formBuilder->createField([
			'type' => FieldType::TEXT,
			'id' => 'playlist_name',
			'name' => 'playlist_name',
			'title' => $this->translator->translate('playlist_name', 'playlists'),
			'label' => $this->translator->translate('playlist_name', 'playlists'),
			'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_PLAYLIST_NAME)
		]);

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_USERNAME))
		{
			$form['UID'] = $this->formBuilder->createField([
				'type' => FieldType::AUTOCOMPLETE,
				'id' => 'UID',
				'name' => 'UID',
				'title' => $this->translator->translate('owner', 'main'),
				'label' => $this->translator->translate('owner', 'main'),
				'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_USERNAME),
				'data-label' => ''
			]);
		}

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_PLAYLIST_MODE))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::DROPDOWN,
				'id' => 'playlist_mode',
				'name' => 'playlist_mode',
				'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_PLAYLIST_MODE),
				'options' => $this->translator->translateArrayForOptions('playlist_mode_selects', 'playlists')
			]);
		}

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_COMPANY_ID))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::HIDDEN,
				'id' => 'company_id',
				'name' => 'company_id',
				'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_COMPANY_ID),
			]);
		}

		return $form;
	}

}
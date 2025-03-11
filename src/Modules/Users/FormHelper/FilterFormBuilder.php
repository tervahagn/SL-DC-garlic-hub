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

namespace App\Modules\Users\FormHelper;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Users\Services\AclValidator;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class FilterFormBuilder
{
	private FormBuilder $formBuilder;
	private Translator $translator;
	private FilterParameters $parameters;
	private int $UID;
	private string $username;

	public function __construct(FilterParameters $parameters, FormBuilder $formBuilder)
	{
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

	/**
	 * @throws ModuleException
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function buildForm(): array
	{
		$form = $this->collectFormElements();
		return $this->formBuilder->createFormular($form);
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function collectFormElements(): array
	{
		$form       = [];
		$form['username'] = $this->formBuilder->createField([
			'type'  => FieldType::TEXT,
			'id'    => FilterParameters::PARAMETER_USERNAME,
			'name'  => FilterParameters::PARAMETER_USERNAME,
			'title' => $this->translator->translate(FilterParameters::PARAMETER_USERNAME, 'main'),
			'label' => $this->translator->translate(FilterParameters::PARAMETER_USERNAME, 'main'),
			'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_USERNAME)
		]);

		$form['UID'] = $this->formBuilder->createField([
			'type'  => FieldType::TEXT,
			'id'    => FilterParameters::PARAMETER_EMAIL,
			'name'  => FilterParameters::PARAMETER_EMAIL,
			'title' => $this->translator->translate(FilterParameters::PARAMETER_EMAIL, 'users'),
			'label' => $this->translator->translate(FilterParameters::PARAMETER_EMAIL, 'users'),
			'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_EMAIL)
		]);

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_FIRSTNAME))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::TEXT,
				'id' => FilterParameters::PARAMETER_FIRSTNAME,
				'name' => FilterParameters::PARAMETER_FIRSTNAME,
				'title' => $this->translator->translate(FilterParameters::PARAMETER_FIRSTNAME, 'users'),
				'label' => $this->translator->translate(FilterParameters::PARAMETER_FIRSTNAME, 'users'),
				'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_FIRSTNAME)
			]);
		}

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_SURNAME))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::TEXT,
				'id' => FilterParameters::PARAMETER_SURNAME,
				'name' => FilterParameters::PARAMETER_SURNAME,
				'title' => $this->translator->translate(FilterParameters::PARAMETER_SURNAME, 'users'),
				'label' => $this->translator->translate(FilterParameters::PARAMETER_SURNAME, 'users'),
				'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_SURNAME)
			]);
		}

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_COMPANY_NAME))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::TEXT,
				'id' => FilterParameters::PARAMETER_COMPANY_NAME,
				'name' => FilterParameters::PARAMETER_COMPANY_NAME,
				'title' => $this->translator->translate(FilterParameters::PARAMETER_COMPANY_NAME, 'users'),
				'label' => $this->translator->translate(FilterParameters::PARAMETER_COMPANY_NAME, 'users'),
				'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_COMPANY_NAME)
			]);
		}

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_COMPANY_ID))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::DROPDOWN,
				'id' => FilterParameters::PARAMETER_COMPANY_ID,
				'name' => FilterParameters::PARAMETER_COMPANY_ID,
				'title' => $this->translator->translate('belongs_company', 'main'),
				'label' => $this->translator->translate('belongs_company', 'main'),
				'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_COMPANY_ID),
				'options' => []
			]);
		}

		if ($this->parameters->hasParameter(FilterParameters::PARAMETER_STATUS))
		{
			$form['playlist_mode'] = $this->formBuilder->createField([
				'type' => FieldType::DROPDOWN,
				'id' => FilterParameters::PARAMETER_STATUS,
				'name' => FilterParameters::PARAMETER_STATUS,
				'title' => $this->translator->translate('belongs_company', 'main'),
				'label' => $this->translator->translate('belongs_company', 'main'),
				'value' => $this->parameters->getValueOfParameter(FilterParameters::PARAMETER_STATUS),
				'options' => $this->translator->translateArrayForOptions('playlist_mode_selects', 'users')
			]);
		}

		return $form;
	}

}
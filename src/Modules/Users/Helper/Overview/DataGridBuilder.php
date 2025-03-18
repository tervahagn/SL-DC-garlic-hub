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

namespace App\Modules\Users\Helper\Overview;

use App\Framework\Core\Config\Config;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\DataGrid\BuildServiceLocator;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\Html\FieldType;

class DataGridBuilder
{
	private BuildServiceLocator $buildServiceLocator;
	private Translator $translator;
	private Parameters $parameters;
	private Config $config;
	private array $dataGridBuild = [];

	public function __construct(BuildServiceLocator $buildServiceLocator, Parameters $parameters, Translator $translator, Config $config)
	{
		$this->buildServiceLocator  = $buildServiceLocator;
		$this->parameters   = $parameters;
		$this->translator   = $translator;
		$this->config       = $config;
	}
	public function getDataGridBuild(): array
	{
		return $this->dataGridBuild;
	}

	public function collectFormElements(): void
	{
		$form       = [];
		$form[Parameters::PARAMETER_USERNAME] = $this->buildServiceLocator->getFormBuilder()->createField([
			'type'  => FieldType::TEXT,
			'id'    => Parameters::PARAMETER_USERNAME,
			'name'  => Parameters::PARAMETER_USERNAME,
			'title' => $this->translator->translate(Parameters::PARAMETER_USERNAME, 'main'),
			'label' => $this->translator->translate(Parameters::PARAMETER_USERNAME, 'main'),
			'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_USERNAME)
		]);

		$form[Parameters::PARAMETER_EMAIL] = $this->buildServiceLocator->getFormBuilder()->createField([
			'type'  => FieldType::TEXT,
			'id'    => Parameters::PARAMETER_EMAIL,
			'name'  => Parameters::PARAMETER_EMAIL,
			'title' => $this->translator->translate(Parameters::PARAMETER_EMAIL, 'users'),
			'label' => $this->translator->translate(Parameters::PARAMETER_EMAIL, 'users'),
			'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_EMAIL)
		]);

		if ($this->parameters->hasParameter(Parameters::PARAMETER_FIRSTNAME))
		{
			$form[Parameters::PARAMETER_FIRSTNAME] = $this->buildServiceLocator->getFormBuilder()->createField([
				'type' => FieldType::TEXT,
				'id' => Parameters::PARAMETER_FIRSTNAME,
				'name' => Parameters::PARAMETER_FIRSTNAME,
				'title' => $this->translator->translate(Parameters::PARAMETER_FIRSTNAME, 'users'),
				'label' => $this->translator->translate(Parameters::PARAMETER_FIRSTNAME, 'users'),
				'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_FIRSTNAME)
			]);
		}

		if ($this->parameters->hasParameter(Parameters::PARAMETER_SURNAME))
		{
			$form[Parameters::PARAMETER_SURNAME] = $this->buildServiceLocator->getFormBuilder()->createField([
				'type' => FieldType::TEXT,
				'id' => Parameters::PARAMETER_SURNAME,
				'name' => Parameters::PARAMETER_SURNAME,
				'title' => $this->translator->translate(Parameters::PARAMETER_SURNAME, 'users'),
				'label' => $this->translator->translate(Parameters::PARAMETER_SURNAME, 'users'),
				'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_SURNAME)
			]);
		}

		if ($this->parameters->hasParameter(Parameters::PARAMETER_COMPANY_NAME))
		{
			$form[Parameters::PARAMETER_COMPANY_NAME] = $this->buildServiceLocator->getFormBuilder()->createField([
				'type' => FieldType::TEXT,
				'id' => Parameters::PARAMETER_COMPANY_NAME,
				'name' => Parameters::PARAMETER_COMPANY_NAME,
				'title' => $this->translator->translate(Parameters::PARAMETER_COMPANY_NAME, 'users'),
				'label' => $this->translator->translate(Parameters::PARAMETER_COMPANY_NAME, 'users'),
				'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_COMPANY_NAME)
			]);
		}

		if ($this->parameters->hasParameter(BaseFilterParameters::PARAMETER_COMPANY_ID))
		{
			$form[Parameters::PARAMETER_COMPANY_ID] = $this->buildServiceLocator->getFormBuilder()->createField([
				'type' => FieldType::DROPDOWN,
				'id' => Parameters::PARAMETER_COMPANY_ID,
				'name' => Parameters::PARAMETER_COMPANY_ID,
				'title' => $this->translator->translate('belongs_company', 'main'),
				'label' => $this->translator->translate('belongs_company', 'main'),
				'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_COMPANY_ID),
				'options' => []
			]);
		}

		if ($this->parameters->hasParameter(Parameters::PARAMETER_STATUS))
		{
			$form[Parameters::PARAMETER_STATUS] = $this->buildServiceLocator->getFormBuilder()->createField([
				'type' => FieldType::DROPDOWN,
				'id' => Parameters::PARAMETER_STATUS,
				'name' => Parameters::PARAMETER_STATUS,
				'title' => $this->translator->translate(Parameters::PARAMETER_STATUS, 'users'),
				'label' => $this->translator->translate(Parameters::PARAMETER_STATUS, 'users'),
				'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_STATUS),
				'options' => $this->translator->translateArrayForOptions(Parameters::PARAMETER_STATUS.'_selects', 'users')
			]);
		}

		$this->dataGridBuild['form'] = $form;
	}

	public function createTableFields(): static
	{
		$this->buildServiceLocator->getResultsBuilder()->createField('username',true);
		$this->buildServiceLocator->getResultsBuilder()->createField('created_at', true);
		$this->buildServiceLocator->getResultsBuilder()->createField('status', false);
		if ($this->config->getEdition() === Config::PLATFORM_EDITION_CORE || $this->config->getEdition() === Config::PLATFORM_EDITION_ENTERPRISE)
		{
			$this->buildServiceLocator->getResultsBuilder()->createField('firstname', false);
			$this->buildServiceLocator->getResultsBuilder()->createField('surname', false);
			$this->buildServiceLocator->getResultsBuilder()->createField('company_name', false);
		}

		$this->dataGridBuild['header'] = $this->buildServiceLocator->getResultsBuilder()->getHeaderFields();

		return $this;
	}

	public function createPagination(int $resultCount): void
	{
		$this->dataGridBuild['pager'] = $this->buildServiceLocator->getPaginationBuilder()->configure($this->parameters, $resultCount, true)
			->buildPagerLinks()
			->getPagerLinks();
	}

	public function createDropDown(): void
	{
		$this->dataGridBuild['dropdown'] = $this->buildServiceLocator->getPaginationBuilder()
			->createDropDown()
			->getDropDownSettings();
	}
}
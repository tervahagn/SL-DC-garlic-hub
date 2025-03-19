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

namespace App\Modules\Playlists\Helper\Datatable;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Datatable\BuildServiceLocator;
use App\Framework\Utils\FormParameters\BaseParameters;
use App\Framework\Utils\Html\FieldType;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

class DatatableBuilder
{
	private BuildServiceLocator $buildServiceLocator;
	private Translator $translator;
	private Parameters $parameters;
	private array $dataGridBuild = [];

	public function __construct(BuildServiceLocator $buildServiceLocator, Parameters $parameters, Translator $translator)
	{
		$this->buildServiceLocator  = $buildServiceLocator;
		$this->parameters   = $parameters;
		$this->translator   = $translator;
	}

	public function getDataGridBuild(): array
	{
		return $this->dataGridBuild;
	}

	/**
	 * @throws CoreException
	 * @throws FrameworkException
	 * @throws ModuleException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 */
	public function collectFormElements(): void
	{
		$form       = [];
		$form[Parameters::PARAMETER_PLAYLIST_NAME] = $this->buildServiceLocator->getFormBuilder()->createField([
			'type' => FieldType::TEXT,
			'id' => Parameters::PARAMETER_PLAYLIST_NAME,
			'name' => Parameters::PARAMETER_PLAYLIST_NAME,
			'title' => $this->translator->translate(Parameters::PARAMETER_PLAYLIST_NAME, 'playlists'),
			'label' => $this->translator->translate(Parameters::PARAMETER_PLAYLIST_NAME, 'playlists'),
			'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_PLAYLIST_NAME)
		]);

		if ($this->parameters->hasParameter(BaseParameters::PARAMETER_UID))
		{
			$form[BaseParameters::PARAMETER_UID] = $this->buildServiceLocator->getFormBuilder()->createField([
				'type' => FieldType::AUTOCOMPLETE,
				'id' => 'UID',
				'name' => 'UID',
				'title' => $this->translator->translate('owner', 'main'),
				'label' => $this->translator->translate('owner', 'main'),
				'value' => $this->parameters->getValueOfParameter(BaseParameters::PARAMETER_UID),
				'data-label' => ''
			]);
		}

		if ($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_MODE))
		{
			$form[Parameters::PARAMETER_PLAYLIST_MODE] = $this->buildServiceLocator->getFormBuilder()->createField([
				'type' => FieldType::DROPDOWN,
				'id' => Parameters::PARAMETER_PLAYLIST_MODE,
				'name' => Parameters::PARAMETER_PLAYLIST_MODE,
				'title' => $this->translator->translate(Parameters::PARAMETER_PLAYLIST_MODE, 'playlists'),
				'label' => $this->translator->translate(Parameters::PARAMETER_PLAYLIST_MODE, 'playlists'),
				'value' => $this->parameters->getValueOfParameter(Parameters::PARAMETER_PLAYLIST_MODE),
				'options' => $this->translator->translateArrayForOptions(Parameters::PARAMETER_PLAYLIST_MODE.'_selects', 'playlists')
			]);
		}

		$this->dataGridBuild['form'] = $form;
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws Exception
	 */
	public function createTableFields(): static
	{
		$this->buildServiceLocator->getResultsBuilder()->createField('playlist_name', true);

		if ($this->parameters->hasParameter('UID'))
			$this->buildServiceLocator->getResultsBuilder()->createField('UID', true);

		$this->buildServiceLocator->getResultsBuilder()->createField('playlist_mode', true);
		$this->buildServiceLocator->getResultsBuilder()->createField('duration', false);

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
		$this->dataGridBuild['dropdown'] = $this->buildServiceLocator->getPaginationBuilder()->createDropDown()->getDropDownSettings();
	}

}
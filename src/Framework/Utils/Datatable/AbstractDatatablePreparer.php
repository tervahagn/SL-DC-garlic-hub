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

namespace App\Framework\Utils\Datatable;

use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Psr\SimpleCache\InvalidArgumentException;

abstract class AbstractDatatablePreparer
{
	protected PrepareService $prepareService;
	protected string $moduleName;
	protected Translator $translator;
	private BaseFilterParameters $parameters;

	public function __construct(string $moduleName, PrepareService $prepareService, BaseFilterParameters $parameters)
	{
		$this->moduleName     = $moduleName;
		$this->prepareService = $prepareService;
		$this->parameters     = $parameters;
		$this->prepareService->configure($parameters, 'playlists');
	}

	public function setTranslator(Translator $translator): AbstractDatatablePreparer
	{
		$this->translator = $translator;
		return $this;
	}

	public function prepareFilterForm(array $dataGridBuild): array
	{
		return $this->prepareService->prepareForm($dataGridBuild);
	}

	/**
	 * @throws ModuleException
	 */
	public function preparePagination(array $paginationLinks, array $dropDownSettings): array
	{
		return $this->prepareService->preparePagination($paginationLinks, $dropDownSettings);
	}


	public function prepareTableHeader(array $fields, array $langModules): array
	{
		return $this->prepareService->prepareDatatableHeader($fields, $langModules);
	}

	/**
	 * @throws CoreException
	 * @throws PhpfastcacheSimpleCacheException
	 * @throws InvalidArgumentException
	 * @throws FrameworkException
	 */
	public function prepareAdd(string $iconClass = 'folder-plus'): array
	{
		return [
			'ADD_BI_ICON' => $iconClass,
			'LANG_ELEMENTS_ADD_LINK' =>	$this->translator->translate('add', $this->moduleName),
			'ELEMENTS_ADD_LINK' => '#'

		];
	}

	/**
	 * @throws ModuleException
	 */
	public function prepareSort(): array
	{
		return [
			'column' => $this->parameters->getValueOfParameter(BaseFilterParametersInterface::PARAMETER_SORT_COLUMN),
			'order' =>  $this->parameters->getValueOfParameter(BaseFilterParametersInterface::PARAMETER_SORT_ORDER)
		];
	}

	/**
	 * @throws ModuleException
	 */
	public function preparePage(): array
	{
		return [
			'current' => $this->parameters->getValueOfParameter(BaseFilterParametersInterface::PARAMETER_ELEMENTS_PAGE),
			'num_elements' => $this->parameters->getValueOfParameter(BaseFilterParametersInterface::PARAMETER_ELEMENTS_PER_PAGE),
		];
	}


	abstract public function prepareTableBody(array $currentFilterResults, array $fields, $currentUID): array;

}
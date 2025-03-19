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

use App\Framework\Core\Acl\AbstractAclValidatorInterface;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseFilterParameters;

abstract class AbstractDatatableFormatter
{
	protected FormatterServiceLocator $formatterServiceLocator;
	protected Translator $translator;
	protected AbstractAclValidatorInterface $aclValidator;
	protected string $moduleName;
	private BaseFilterParameters $parameters;

	public function __construct(string $moduleName, FormatterServiceLocator $formatterServiceLocator, Translator $translator, AbstractAclValidatorInterface $aclValidator)
	{
		$this->moduleName = $moduleName;
		$this->formatterServiceLocator = $formatterServiceLocator;
		$this->translator = $translator;
		$this->aclValidator = $aclValidator;
	}

	public function formatFilterForm(array $dataGridBuild): array
	{
		return $this->formatterServiceLocator->getFormBuilder()->formatForm($dataGridBuild);
	}

	public function configurePagination(BaseFilterParameters $parameters): void
	{
		$this->parameters = $parameters;
		$this->formatterServiceLocator->getPaginationFormatter()
			->setSite($this->moduleName)
			->setBaseFilter($parameters);

	}
	/**
	 * @throws ModuleException
	 */
	public function formatPaginationDropDown(array $dropDownSettings): array
	{
		return $this->formatterServiceLocator->getPaginationFormatter()->formatDropdown($dropDownSettings);
	}

	/**
	 * @throws ModuleException
	 */
	public function formatPaginationLinks(array $paginationLinks): array
	{
		return $this->formatterServiceLocator->getPaginationFormatter()->formatLinks($paginationLinks);
	}

	public function formatTableHeader(array $fields, array $langModules): array
	{
		$this->formatterServiceLocator->getHeaderFormatter()->configure($this->parameters, $this->moduleName, $langModules);
		return $this->formatterServiceLocator->getHeaderFormatter()->renderTableHeader($fields);
	}

	public function formatAdd(string $iconClass = 'folder-plus'): array
	{
		return [
			'ADD_BI_ICON' => $iconClass,
			'LANG_ELEMENTS_ADD_LINK' =>	$this->translator->translate('add', $this->moduleName),
			'ELEMENTS_ADD_LINK' => '#'

		];
	}

	abstract public function formatTableBody(array $currentFilterResults, array $fields, $currentUID): array;

}
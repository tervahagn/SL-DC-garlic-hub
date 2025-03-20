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

use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\Datatable\Paginator\Preparer;
use App\Framework\Utils\Datatable\Results\BodyPreparer;
use App\Framework\Utils\Datatable\Results\HeaderPreparer;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\Html\FormBuilder;

/**
 * The `PrepareServiceLocator` class implements the Service Locator pattern, which acts as a central registry for preparing data grids in the application.
 */
class PrepareService
{
	private HeaderPreparer $headerPreparer;
	private BodyPreparer $bodyPreparer;
	private Preparer $paginationPreparer;
	private FormBuilder $formBuilder;
	private string $moduleName;

	private BaseFilterParameters $parameters;

	/**
	 * @param HeaderPreparer $headerFormatter
	 * @param BodyPreparer $bodyFormatter
	 * @param Preparer $paginationFormatter
	 * @param FormBuilder $formBuilder
	 */
	public function __construct(FormBuilder $formBuilder, Preparer $paginationFormatter, HeaderPreparer $headerFormatter, BodyPreparer $bodyFormatter)
	{
		$this->formBuilder         = $formBuilder;
		$this->paginationPreparer = $paginationFormatter;
		$this->headerPreparer     = $headerFormatter;
		$this->bodyPreparer       = $bodyFormatter;
	}

	public function prepareForm(array $datatableForm): array
	{
		return $this->formBuilder->prepareForm($datatableForm);
	}

	public function configure(BaseFilterParameters $parameters, string $moduleName): void
	{
		$this->paginationPreparer->setSite($moduleName)->setBaseFilter($parameters);
		$this->moduleName = $moduleName;
		$this->parameters = $parameters;
	}

	/**
	 * @throws ModuleException
	 */
	public function preparePagination(array $paginationLinks, array $dropDownSettings): array
	{
		return [
			'links' => $this->paginationPreparer->prepareLinks($paginationLinks),
			'dropdown' => $this->paginationPreparer->prepareDropdown($dropDownSettings)
			];
	}

	public function prepareDatatableHeader(array $fields, array $langModules):  array
	{
		$this->headerPreparer->configure($this->parameters, $this->moduleName, $langModules);
		return $this->headerPreparer->prepareTableHeader($fields);
	}

	public function getBodyPreparer(): BodyPreparer
	{
		return $this->bodyPreparer;
	}
}
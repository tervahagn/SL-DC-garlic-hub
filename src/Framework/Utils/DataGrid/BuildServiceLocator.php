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

namespace App\Framework\Utils\DataGrid;

use App\Framework\Utils\Html\FormBuilder;

/**
 * BuildServiceLocator is an implementation of the so-called “Service Locator” design pattern.
 *
 * The class stores twxhree objects of the type:
 * - `FormBuilder` - Creates and formats form fields.
 * - `Paginator\Builder` - Generates paging components for dividing large amounts of data into several pages.
 * - `Results\Builder` - Manages the creation of table-like outputs or result sets.
 */
class BuildServiceLocator
{
	private FormBuilder $formBuilder;
	private Results\Builder $resultsBuilder;
	private Paginator\Builder $paginationBuilder;

	/**
	 * @param FormBuilder $formBuilder
	 * @param Results\Builder $resultsBuilder
	 * @param Paginator\Builder $paginationManager
	 */
	public function __construct(FormBuilder $formBuilder, Paginator\Builder $paginationBuilder, Results\Builder $resultsBuilder)
	{
		$this->formBuilder = $formBuilder;
		$this->resultsBuilder = $resultsBuilder;
		$this->paginationBuilder = $paginationBuilder;
	}

	public function getFormBuilder(): FormBuilder
	{
		return $this->formBuilder;
	}

	public function getPaginationBuilder(): Paginator\Builder
	{
		return $this->paginationBuilder;
	}

	public function getResultsBuilder(): Results\Builder
	{
		return $this->resultsBuilder;
	}

}
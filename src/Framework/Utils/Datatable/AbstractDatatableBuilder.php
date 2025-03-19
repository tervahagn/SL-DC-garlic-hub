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

use App\Framework\Core\Config\Config;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\Datatable\BuildServiceLocator;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;
use App\Framework\Utils\Html\FieldType;
use App\Modules\Users\Helper\Datatable\Parameters;

abstract class AbstractDatatableBuilder
{
	protected BuildServiceLocator $buildServiceLocator;
	protected Translator $translator;
	protected BaseFilterParametersInterface $parameters;
	protected array $datatableStructure = [];

	public function __construct(BuildServiceLocator $buildServiceLocator, BaseFilterParametersInterface $parameters, Translator $translator)
	{
		$this->buildServiceLocator  = $buildServiceLocator;
		$this->translator           = $translator;
		$this->parameters           = $parameters;
	}
	public function getDatatableStructure(): array
	{
		return $this->datatableStructure;
	}

	abstract public function collectFormElements(): void;

	abstract public function createTableFields(): static;

	public function createPagination(int $resultCount, bool $usePager = true, bool $isShortened = true): void
	{
		$this->datatableStructure['pager'] = $this->buildServiceLocator->getPaginationBuilder()->configure($this->parameters, $resultCount, $usePager, $isShortened)
			->buildPagerLinks()
			->getPagerLinks();
	}

	public function createDropDown(int $min = 10, int $max = 100, int $steps = 10): void
	{
		$this->datatableStructure['dropdown'] = $this->buildServiceLocator->getPaginationBuilder()
			->createDropDown($min, $max, $steps)
			->getDropDownSettings();
	}
}
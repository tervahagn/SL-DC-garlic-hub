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

namespace App\Framework\Utils\FilteredList\Paginator;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseFilterParameters;

class Renderer
{
	private BaseFilterParameters $baseFilter;
	private string $site;

	public function setBaseFilter(BaseFilterParameters $baseFilter): static
	{
		$this->baseFilter = $baseFilter;
		return $this;
	}

	public function setSite(string $site): static
	{
		$this->site = $site;
		return $this;
	}


	/**
	 * @throws ModuleException
	 */
	public function renderLinks(array $pageLinks): array
	{
		$sortSuffix = '&sort_column='.$this->baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_COLUMN).
			'&sort_order='.$this->baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_ORDER).
			'&elements_per_page='.$this->baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE);
		$data = [];
		foreach($pageLinks as $values)
		{
			$data[] = [
				'ELEMENTS_PAGELINK'   => '/'.$this->site.'?elements_page='.$values['page'].$sortSuffix,
				'ELEMENTS_PAGENAME'   => $values['name'],
				'ELEMENTS_PAGENUMBER' => $values['page']
			];
		}

		return $data;
	}

	/**
	 * @throws ModuleException
	 */
	public function renderDropdown(array $dropDownSettings): array
	{
		$sortSuffix = '&sort_column='.$this->baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_COLUMN).
			'&sort_order='.$this->baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_ORDER).
			'&elements_page='.$this->baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PAGE);

		$data = [];
		$currentElementsPerPage = (int) $this->baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE);
		for ($i = $dropDownSettings['min']; $i <= $dropDownSettings['max']; $i += $dropDownSettings['steps'])
		{
			$data[] = [
				'ELEMENTS_PER_PAGE_VALUE' => $i,
				'ELEMENTS_PER_PAGE_DATA_LINK' => '/'.$this->site.'?elements_per_page='.$i.$sortSuffix,
				'ELEMENTS_PER_PAGE_NAME' => $i,
				'ELEMENTS_PER_PAGE_SELECTED' => ($i === $currentElementsPerPage) ? 'selected' : ''
			];
		}
		return $data;
	}
}
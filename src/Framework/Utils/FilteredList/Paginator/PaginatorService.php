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

class PaginatorService
{
	private Creator $creator;
	private Renderer $renderer;
	private BaseFilterParameters $baseFilter;
	private array $pagerLinks;

	/**
	 * @param Creator $creator
	 * @param Renderer $renderer
	 */
	public function __construct(Creator $creator, Renderer $renderer)
	{
		$this->creator = $creator;
		$this->renderer = $renderer;
	}

	public function setBaseFilter(BaseFilterParameters $baseFilter): PaginatorService
	{
		$this->baseFilter = $baseFilter;
		return $this;
	}


	public function create(int $totalItems, bool $usePager = false, bool $shortened = true): void
	{
		$this->pagerLinks = $this->creator->init($this->baseFilter, $totalItems, $usePager, $shortened)
			->buildPagerLinks()
			->getPagerLinks();
	}

	public function renderPagination(string $site): array
	{
		return $this->renderer->render($this->pagerLinks, $site, $this->baseFilter);
	}

	/**
	 * @throws ModuleException
	 */
	public function renderElementsPerSiteDropDown(int $min = 10, int $max = 100, int $steps = 10)
	{
		$data = [];
		$currentElementsPerPage = (int) $this->baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE);
		for ($i = $min; $i <= $max; $i += $steps)
		{
			$data[] = [
				'ELEMENTS_PER_PAGE_VALUE' => $i,
				'ELEMENTS_PER_PAGE_NAME' => $i,
				'ELEMENTS_PER_PAGE_SELECTED' => ($i === $currentElementsPerPage) ? 'selected' : ''
			];
		}
		return $data;
	}



}
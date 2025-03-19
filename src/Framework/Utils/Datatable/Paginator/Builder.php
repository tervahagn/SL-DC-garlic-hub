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

namespace App\Framework\Utils\Datatable\Paginator;

use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Framework\Utils\FormParameters\BaseFilterParametersInterface;

class Builder
{
	private int $currentPage;
	private int $itemsPerPage;
	private int $totalItems;
	private bool $usePager;
	private bool $shortened;
	private array $pagerLinks;
	/**
	 * @var array|int[]
	 */
	private array $dropDownSettings;

	public function configure(BaseFilterParametersInterface $baseFilter, int $totalItems, bool $usePager = false, bool $shortened = true): static
	{
		$this->currentPage  = max(1, $baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PAGE));
		$this->itemsPerPage = max(1, $baseFilter->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE));
		$this->totalItems   = max(0, $totalItems);
		$this->usePager     = $usePager;
		$this->shortened    = $shortened;

		return $this;
	}

	public function getPagerLinks(): array
	{
		return $this->pagerLinks;
	}

	public function getDropDownSettings(): array
	{
		return $this->dropDownSettings;
	}

	public function createDropDown(int $min = 10, int $max = 100, int $steps = 10): static
	{
		$this->dropDownSettings = ['min' => $min, 'max' => $max, 'steps' => $steps];

		return $this;
	}

	public function buildPagerLinks(): static
	{
		$this->pagerLinks = [];
		$maxPages = max(1, (int) ceil($this->totalItems / $this->itemsPerPage));

		if ($this->usePager && $this->currentPage > 1)
		{
			$this->pagerLinks[] = ['name' => '«', 'page' => 1];
			$this->pagerLinks[] = ['name' => '‹', 'page' => $this->currentPage - 1];
		}

		for ($i = 1; $i <= $maxPages; $i++)
		{
			if (!$this->shortened || $this->isPageInRange($i, $maxPages))
				$this->pagerLinks[] = ['name' => (string) $i, 'page' => $i, 'active' => ($i === $this->currentPage)];
		}

		if ($this->usePager && $this->currentPage < $maxPages)
		{
			$this->pagerLinks[] = ['name' => '›', 'page' => $this->currentPage + 1];
			$this->pagerLinks[] = ['name' => '»', 'page' => $maxPages];
		}

		return $this;
	}

	private function isPageInRange(int $page, int $maxPages): bool
	{
		return $page <= 3 || $page >= ($maxPages - 2) || abs($this->currentPage - $page) <= 2;
	}
}

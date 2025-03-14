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

namespace App\Framework\Utils\FilteredList\Results;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseFilterParameters;

class UrlBuilder
{
	protected string $site = '';
	private BaseFilterParameters $filterParameters;
	private array $additionalUrlParameters;


	public function setFilterParameters(BaseFilterParameters $filterParameters): UrlBuilder
	{
		$this->filterParameters = $filterParameters;
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
	public function buildSortUrl(HeaderField $headerField, string $sort_order): string
	{
		$params = array(
			'elements_page' => $this->filterParameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PAGE),
			'sort_column' => $headerField->getName(),
			'sort_order' => $sort_order,
			'elements_per_page' => $this->filterParameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE)
		);

		if ($this->hasAdditionalUrlParameters())
		{
			$params = array_merge($params, $this->additionalUrlParameters);
		}

		return $this->site . '?' . http_build_query($params);
	}

	public function addAdditionalUrlParameter($key, $value): static
	{
		$this->additionalUrlParameters[$key] = $value;
		return $this;
	}

	public function clearAdditionalUrlParameters(): static
	{
		$this->additionalUrlParameters = [];
		return $this;
	}

	public function hasAdditionalUrlParameters(): bool
	{
		return (!empty($this->additionalUrlParameters));
	}
}
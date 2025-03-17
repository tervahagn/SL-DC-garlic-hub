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

namespace App\Framework\Utils\DataGrid\Results;

use App\Framework\Exceptions\ModuleException;
use App\Framework\Utils\FormParameters\BaseFilterParameters;

class HeaderDataFormatter
{
	private BaseFilterParameters $filterParameter;
	private TranslatorManager $translatorManager;
	private UrlBuilder $urlBuilder;
	private string $site;

	/**
	 * @param TranslatorManager $translatorManager
	 * @param UrlBuilder $urlBuilder
	 */
	public function __construct(TranslatorManager $translatorManager, UrlBuilder $urlBuilder)
	{
		$this->translatorManager = $translatorManager;
		$this->urlBuilder = $urlBuilder;
	}

	public function configure(BaseFilterParameters $filterParameter, string $site, array $languageModules): void
	{
		$this->filterParameter = $filterParameter;
		$this->site = $site;
		foreach ($languageModules as $module)
		{
			$this->translatorManager->addLanguageModule($module);
		}
	}

	public function renderTableHeader(array $tableHeaderFields): array
	{
		$header = [];
		/* @var $headerField HeaderField */
		foreach($tableHeaderFields as $headerField)
		{
			$headerFieldName = $headerField->getName();
			$controlName     = ['CONTROL_NAME' => $headerFieldName];

			if ($headerField->isSortable())
				$controlName['if_sortable'] = $this->renderSortableHeaderField($headerField);
			else
				$controlName['LANG_CONTROL_NAME_2'] = $this->translatorManager->translate($headerField);

			$header[] = $controlName;
		}

		return $header;
	}

	/**
	 * @throws ModuleException
	 */
	protected function renderSortableHeaderField(HeaderField $headerField):array
	{
		$sortableData = array();

		if ($this->filterParameter->getValueOfParameter('sort_column') == $headerField->getName())
		{
			if ($this->filterParameter->getValueOfParameter('sort_order') == 'asc')
			{
				$sort_order_tmp = 'desc';
				$sortableData['SORTABLE_ORDER']    = '▼';
			}
			else
			{
				$sort_order_tmp = 'asc';
				$sortableData['SORTABLE_ORDER']    = '▲';
			}
		}
		else
		{
			$sort_order_tmp = 'asc';
			$sortableData['SORTABLE_ORDER'] = '◆';
		}

		$sortableData['SORT_CONTROL_NAME']         = $headerField->getName();
		$this->urlBuilder->setFilterParameters($this->filterParameter);
		$this->urlBuilder->setSite($this->site);
		$sortableData['LINK_CONTROL_SORT_ORDER']   = $this->urlBuilder->buildSortUrl($headerField, $sort_order_tmp);

		$sortableData['LANG_CONTROL_NAME']         = $this->translatorManager->translate($headerField);

		return $sortableData;
	}

}
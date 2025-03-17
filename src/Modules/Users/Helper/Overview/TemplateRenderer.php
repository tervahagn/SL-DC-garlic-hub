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

namespace App\Modules\Users\Helper\Overview;

use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Users\Helper\Overview\Parameters;

class TemplateRenderer
{
	private Translator $translator;
	private readonly Parameters $parameters;


	public function __construct(Translator $translator, Parameters $parameters)
	{
		$this->translator = $translator;
		$this->parameters = $parameters;
	}

	private function renderTemplate(): array
	{
		$elements = $this->formBuilder->init($this->translator, $this->session)->buildForm();

		$title = $this->translator->translate('users_overview', 'main');
		$total = $this->usersService->getCurrentTotalResult();
		$this->paginatorService->init($this->parameters)->createPagination($total);

		return [
			'main_layout' => [
				'LANG_PAGE_TITLE' => $title,
				'additional_css' => ['/css/users/overview.css'],
				'footer_modules' => ['/js/users/overview/init.js']
			],
			'this_layout' => [
				'template' => 'users/overview', // Template-name
				'data' => [
					'LANG_PAGE_HEADER' => $title,
					'FORM_ACTION' => '/users',
					'element_hidden' => $elements['hidden'],
					'form_element' => $elements['visible'],
					'LANG_ELEMENTS_FILTER' => $this->translator->translate('filter', 'main'),
					'SORT_COLUMN' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_COLUMN),
					'SORT_ORDER' =>  $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_SORT_ORDER),
					'ELEMENTS_PAGE' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PAGE),
					'ELEMENTS_PER_PAGE' => $this->parameters->getValueOfParameter(BaseFilterParameters::PARAMETER_ELEMENTS_PER_PAGE),
					'form_button' => [
						[
							'ELEMENT_BUTTON_TYPE' => 'submit',
							'ELEMENT_BUTTON_NAME' => 'submit',
						]
					],
					'elements_per_page' => $this->paginatorService->renderPaginationDropDown(),
					'add_allowed' => [
						'ADD_BI_ICON' => 'person-add',
						'LANG_ELEMENTS_ADD_LINK' =>	$this->translator->translate('add', 'users'),
						'ELEMENTS_ADD_LINK' => '#'

					],
					'LANG_ELEMENTS_PER_PAGE' => $this->translator->translate('elements_per_page', 'main'),
					'LANG_COUNT_SEARCH_RESULTS' => sprintf($this->translator->translateWithPlural('count_search_results', 'users', $total), $total),
					'elements_pager' => $this->paginatorService->renderPaginationLinks('users'),
					'elements_result_header' => $this->renderHeader(),
					'elements_results' => $this->renderBody()
				]
			]
		];
	}

	private function renderHeader(): array
	{
		$this->resultsList->createFields($this->session->get('user')['UID']);
		return $this->resultsList->renderTableHeader($this->parameters, 'users', $this->translator);
	}

	private function renderBody(): array
	{
		return $this->resultsList->renderTableBody($this->usersService->getCurrentFilterResults());
	}


}
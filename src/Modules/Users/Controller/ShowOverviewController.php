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

namespace App\Modules\Users\Controller;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Utils\FilteredList\Paginator\PaginationManager;
use App\Framework\Utils\FormParameters\BaseFilterParameters;
use App\Modules\Users\FormHelper\FilterFormBuilder;
use App\Modules\Users\Services\ResultsList;
use App\Modules\Users\FormHelper\FilterParameters;
use App\Modules\Users\Services\UsersOverviewService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;

class ShowOverviewController
{
	private readonly FilterFormBuilder $formBuilder;
	private readonly FilterParameters $parameters;
	private readonly UsersOverviewService $usersService;
	private readonly PaginationManager $paginatorService;
	private readonly ResultsList $resultsList;
	private Translator $translator;
	private Session $session;
	private Messages $flash;
	public function __construct(FilterFormBuilder $formBuilder, FilterParameters $parameters, UsersOverviewService $usersService, PaginationManager $paginatorService, ResultsList $resultsList)
	{
		$this->formBuilder      = $formBuilder;
		$this->parameters       = $parameters;
		$this->usersService     = $usersService;
		$this->paginatorService = $paginatorService;
		$this->resultsList      = $resultsList;
	}

	public function show(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$this->parameters->setUserInputs($_GET);
		$this->parameters->parseInputFilterAllUsers();

		$this->setImportantAttributes($request);
		$this->usersService->loadUsersForOverview($this->parameters);

		$data = $this->buildForm();

		$response->getBody()->write(serialize($data));
		return $response->withHeader('Content-Type', 'text/html');
	}

	private function buildForm(): array
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
					'elements_per_page' => $this->paginatorService->renderElementsPerSiteDropDown(),
					'add_allowed' => [
						'ADD_BI_ICON' => 'person-add',
						'LANG_ELEMENTS_ADD_LINK' =>	$this->translator->translate('add', 'users'),
						'ELEMENTS_ADD_LINK' => '#'

					],
					'LANG_ELEMENTS_PER_PAGE' => $this->translator->translate('elements_per_page', 'main'),
					'LANG_COUNT_SEARCH_RESULTS' => sprintf($this->translator->translateWithPlural('count_search_results', 'users', $total), $total),
					'elements_pager' => $this->paginatorService->renderPagination('users'),
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

	private function setImportantAttributes(ServerRequestInterface $request): void
	{
		$this->translator = $request->getAttribute('translator');
		$this->session    = $request->getAttribute('session');
		$this->usersService->setUID($this->session->get('user')['UID']);
		$this->flash      = $request->getAttribute('flash');
	}

}